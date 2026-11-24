<?php
require_once "../../clases/Avatar.php";

$r = new ReflectionClass('Avatar');
$c = $r->getConstants();

echo '<pre>';
echo "Avatar cargado desde: " . $r->getFileName() . "\n";
echo "DIR configurada:      " . $c['DIR'] . "\n";
echo "DIR resuelta:         " . (realpath($c['DIR']) ?: '(no existe)') . "\n";
echo "GD activo:            " . (extension_loaded('gd') ? 'sí' : 'NO') . "\n";