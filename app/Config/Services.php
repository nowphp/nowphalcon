<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;

$di = new FactoryDefault();

// Registering a dispatcher
$di->setShared('dispatcher', function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('Single\Controllers\\');

    return $dispatcher;
});

// Register Volt as a service
$di->setShared("voltService",function ($view, $di) {
        $volt = new Volt($view, $di);
        $volt->setOptions(
            ["compiledPath"      => "../data/cache/",]
            );
        return $volt;
});

// Registering the view component
$di->setShared('view', function () {
    $view = new View();
    $view->setViewsDir('../app/Views/');
    $view->registerEngines(
        [
            ".html" => "voltService",
        ]
        );
    return $view;
});

$di->setShared('url', function () {
    $url = new UrlProvider();
    $url->setBaseUri('/');

    return $url;
});

$di->setShared('db', function () {
    return new Database(
        [
            "host"     => "127.0.0.1",
            "username" => "root",
            "password" => "",
            "charset"  => "utf8",
            "port"     => "3306",
            "dbname"   => "stock"
        ]
    );
});


