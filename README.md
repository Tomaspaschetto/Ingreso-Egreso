# Sistema de Registro de Egreso

Aplicación web para registrar salidas de personal y consultar reportes desde un navegador.

## Características

- Login de administrador
- Registro de personas
- Registro de salidas por fecha, hora, tipo, motivo y destino
- Panel de dashboard con resumen general
- Reportes por departamento y historial de salidas
- Base de datos MySQL para uso en XAMPP

## Inicio rápido

1. Coloca la carpeta dentro de `C:\xampp\htdocs`.
2. Inicia Apache y MySQL en XAMPP.
3. Crea la base `registro_egreso` y ejecuta el contenido de `database.sql`.
4. Abre la URL: `http://localhost/Sistema_Registro_Egreso/index.php`
5. Inicia sesión con:
   - Usuario: `admin`
   - Contraseña: `123456`

## Base de datos

La conexión está configurada para MySQL con estos datos por defecto:

- Host: `localhost`
- Base de datos: `registro_egreso`
- Usuario: `root`
- Contraseña: `` (vacía)

El esquema listo se encuentra en `database.sql`.

## Estructura principal

- `index.php` - Inicio de sesión
- `dashboard.php` - Resumen general
- `registrar_salida.php` - Alta de salida
- `personas.php` - Administración de personas
- `reportes.php` - Reportes
- `includes/db.php` - Configuración y acceso a MySQL
- `assets/css/style.css` - Estilos
- `assets/js/app.js` - JavaScript base

## Requisitos

- PHP 8.x
- Apache / XAMPP
- MySQL habilitado

## Nota

La app crea el usuario administrador automáticamente si no existe.
