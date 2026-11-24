<?php
class Avatar {
    private const DIR         = __DIR__ . '/../modules/storage/users/'; // carpeta física donde se guardan
    private const URL         = 'modules/storage/users/';               // misma carpeta, vista desde el navegador
    private const LADO        = 300;                                    // tamaño máximo final (cuadrado, en px)
    private const MAX_BYTES   = 2097152;                                // 2 MB
    private const MAX_PIXELES = 40000000;                               // evita agotar la memoria con imágenes enormes

    // Formatos que se aceptan: solo los que este PHP realmente puede leer
    private static function lectores(): array {
        $mapa = [
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG  => 'imagecreatefrompng',
            IMAGETYPE_GIF  => 'imagecreatefromgif',
            IMAGETYPE_WEBP => 'imagecreatefromwebp',
            IMAGETYPE_BMP  => 'imagecreatefrombmp',
        ];
        if (defined('IMAGETYPE_AVIF')) {
            $mapa[IMAGETYPE_AVIF] = 'imagecreatefromavif';
        }
        return array_filter($mapa, 'function_exists');
    }

    // 0 = no se subió nada | lanza Exception si el archivo no es válido | devuelve el tipo (IMAGETYPE_*) si es válido
    public static function validar(?array $archivo): int {
        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return 0;
        }
        switch ($archivo['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('La imagen supera los 2 MB.');
            default:
                throw new Exception('No se pudo subir la imagen.');
        }
        if ($archivo['size'] > self::MAX_BYTES) {
            throw new Exception('La imagen supera los 2 MB.');
        }

        // Se comprueba el contenido real del archivo, no su extensión
        $info = @getimagesize($archivo['tmp_name']);
        if ($info === false || !isset(self::lectores()[$info[2]])) {
            throw new Exception('El archivo no es una imagen compatible.');
        }
        if ($info[0] * $info[1] > self::MAX_PIXELES) {
            throw new Exception('La imagen tiene demasiados píxeles. Usa una más pequeña.');
        }
        return $info[2];
    }

    // Procesa y guarda modules/storage/users/<id>.png. Devuelve false si no se subió ninguna foto.
    public static function guardar(int $userId, ?array $archivo): bool {
        $tipo = self::validar($archivo);
        if ($tipo === 0 || $userId <= 0) {
            return false;
        }
        if (!extension_loaded('gd')) {
            throw new Exception('Activa la extensión GD en php.ini (extension=gd).');
        }

        @ini_set('memory_limit', '256M');

        $lector = self::lectores()[$tipo];
        $origen = @$lector($archivo['tmp_name']);
        if (!$origen) {
            throw new Exception('No se pudo procesar la imagen.');
        }

        // GIF y PNG de paleta: pasarlos a color real para poder recortarlos bien
        if (!imageistruecolor($origen)) {
            imagepalettetotruecolor($origen);
        }

        // Fotos de celular: corregir la orientación guardada en los datos EXIF (solo JPEG)
        if ($tipo === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif  = @exif_read_data($archivo['tmp_name']);
            $giro  = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
            if ($giro !== 0) {
                $rotada = imagerotate($origen, $giro, 0);
                if ($rotada) {
                    $origen = $rotada;
                }
            }
        }

        // Recorte central cuadrado; nunca se agranda más que la foto original
        $w     = imagesx($origen);
        $h     = imagesy($origen);
        $lado  = min($w, $h);
        $x     = intdiv($w - $lado, 2);
        $y     = intdiv($h - $lado, 2);
        $final = min(self::LADO, $lado);

        $destino = imagecreatetruecolor($final, $final);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        imagefill($destino, 0, 0, imagecolorallocatealpha($destino, 0, 0, 0, 127));
        imagecopyresampled($destino, $origen, 0, 0, $x, $y, $final, $final, $lado, $lado);

        if (!is_dir(self::DIR) && !mkdir(self::DIR, 0775, true)) {
            throw new Exception('No se pudo crear la carpeta de avatares.');
        }
        if (!imagepng($destino, self::DIR . $userId . '.png', 6)) {
            throw new Exception('No se pudo guardar la imagen.');
        }
        return true;
    }

    public static function eliminar(int $userId): void {
        $archivo = self::DIR . $userId . '.png';
        if (file_exists($archivo)) {
            @unlink($archivo);
        }
    }

    // Ruta pública con "?v=" para evitar caché; null si no tiene foto o el archivo no es una imagen válida
    public static function url(int $userId): ?string {
        $archivo = self::DIR . $userId . '.png';
        if (!is_file($archivo) || @getimagesize($archivo) === false) {
            return null;
        }
        return self::URL . $userId . '.png?v=' . filemtime($archivo);
    }
}