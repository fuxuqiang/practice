#!/usr/bin/env php
<?php

$routeFile = require __DIR__ . '/src/app.php';

if ($argv[1] == 'route') {
    (new \Fuxuqiang\Framework\Route\Router($routeFile))->handle('App\Controller');
} else {
    $command = '\App\Command\\' . str_replace('/', '\\', $argv[1]);
    $args = [];
    $method = new ReflectionMethod($command, 'handle');
    foreach ($method->getParameters() as $key => $param) {
        if ($type = $param->getType()) {
            $args[] = new $type->getName();
        } elseif ($param->isDefaultValueAvailable()) {
            $args[] = $param->getDefaultValue();
        } else {
            $args[] = $argv[2] ?? null;
        }
    }
    try {
        $method->invokeArgs(new $command, $args);
    } catch (Throwable $th) {
        handleThrowable($th);
    }
}
