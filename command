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
        $args[] = match (true) {
            !is_null($type = $param->getType()) => new ($type->getName()),
            $param->isDefaultValueAvailable() => $param->getDefaultValue(),
            default => $argv[$key+2] ?? null,
        };
    }
    $method->invokeArgs(new $command, $args);
}
