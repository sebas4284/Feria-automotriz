<?php

// Front controller de respaldo en la raíz del sitio.
// Hostinger despliega este repo completo en la raíz pública, no solo /public.
// El .htaccess reescribe todo hacia /public/ cuando mod_rewrite está activo,
// pero si el hosting no aplica ese .htaccess (mod_rewrite/AllowOverride
// deshabilitado, permisos incorrectos), Apache no tiene ningún index.php en
// la raíz y responde 403 al no poder listar el directorio. Este archivo
// arranca Laravel directamente desde la raíz para que ese caso también
// funcione, sin depender de que la reescritura a /public tenga efecto.

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
