#!/usr/bin/env php
<?php

$routeFile = require __DIR__ . '/src/app.php';

if ($argv[1] == 'route') {
    (new \Fuxuqiang\Framework\Route\Router($routeFile))->handle('App\Controller');
} else {
    $command = '\App\Command\\' . str_replace('/', '\\', $argv[1]);
    (new ReflectionMethod($command, 'handle'))->invokeArgs(new $command, array_slice($argv, 2));
}
