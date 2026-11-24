# 🛒 Sistema de Ventas e Inventario (PHP Nativo MVC)

Sistema web administrativo desarrollado desde cero bajo el patrón de arquitectura **Modelo-Vista-Controlador (MVC)** en PHP nativo, diseñado para la gestión integral de productos, control de stock, registro de ventas, generación de recibos en PDF y administración de usuarios con roles.

## 🚀 Características Principales
* **Arquitectura MVC:** Separación limpia entre lógica de negocio, acceso a datos e interfaz de usuario.
* **Módulo de Ventas y Facturación:** Registro de ventas en tiempo real con actualización automática de inventario y exportación de comprobantes en **PDF** y **Excel**.
* **Gestión de Usuarios y Seguridad:** Sistema de autenticación, control de acceso por roles y estados de usuario.
* **Paginación y Búsqueda Dinámica:** Filtrado rápido de registros mediante JavaScript asíncrono.

## 🛠️ Tecnologías Utilizadas
* **Backend:** PHP 8+ (Nativo / POO)
* **Base de Datos:** MySQL
* **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap (Plantilla CoreUI)
* **Librerías:** Dompdf (Generación de reportes PDF)

## ⚙️ Instalación en Entorno Local
1. Clona este repositorio en tu servidor local (`htdocs` en XAMPP o `www` en Laragon):
   ```bash
   git clone https://github.com
   ```
2. Importa el archivo `database.sql` en tu gestor de MySQL (phpMyAdmin / MySQL Workbench).
3. Configura tus credenciales locales en el archivo de conexión dentro de la carpeta `config/`.
4. Accede desde tu navegador a `http://localhost/Sistema-ventas-inventario-PHP-nativo`.
