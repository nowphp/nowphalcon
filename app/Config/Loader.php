<?php

use Phalcon\Loader;
define('BASE_PATH', realpath(__DIR__.'/../../'));
define('VENDOR_PATH', BASE_PATH . '/vendor');
if ( file_exists(VENDOR_PATH . '/autoload.php')) {
    require VENDOR_PATH . '/autoload.php';
}
$loader = new Loader();

$loader->registerNamespaces(
    [
        'Single\Controllers' => '../app/Controllers/',
        'Single\Models'      => '../app/Models/'
    ]
);

$loader->register();
