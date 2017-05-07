<?php

use Phalcon\Loader;

$loader = new Loader();

$loader->registerNamespaces(
    [
        'Single\Controllers' => '../app/Controllers/',
        'Single\Models'      => '../app/Models/'
    ]
);

$loader->register();
