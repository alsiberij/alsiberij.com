<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('WEB', 'http://alsiberijlocal/');
define('EMAIL', 'ceo@alsiberij.com');
define('ROOT', __DIR__.'/');
define('VIEWLIFETIME', 360);

spl_autoload_register(function ($className) {
    $folders = array(
        'utils',

        'controllers',
        'controllers/API',
        'controllers/API/News',
        'controllers/API/Account',
        'controllers/API/Store',

        'entities',
    );

    foreach ($folders as $folder) {
        $path = ROOT.$folder.'/'.$className.'.php';
        if (file_exists($path)) {
            require_once($path);
            break;
        }
    }
});

if (session_start()) {
    $router = new Router();
    $router->run();
} else {
    http_response_code(424);
    echo 'Session can not be created';
}

