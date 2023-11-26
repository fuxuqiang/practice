#!/usr/bin/env php
<?php

require __DIR__ . '/src/initError.php';

$routeFile = require __DIR__ . '/src/setting.php';

if ($argv[1] == 'route') {
    (new \Fuxuqiang\Framework\Route\Router($routeFile))->handle('App\Controller');
} else {
    $command = '\App\Command\\' . str_replace('/', '\\', $argv[1]);
    (new ReflectionMethod($command, 'handle'))->invokeArgs(new $command, array_slice($argv, 2));
}
