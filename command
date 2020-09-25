#!/usr/bin/env php
<?php

require __DIR__ . '/src/env.php';
require __DIR__ . '/src/app.php';

try {
    // 执行
    $command = '\App\Command\\' . str_replace('/', '\\', $argv[1]);
    $method = new ReflectionMethod($command, 'handle');
    $args = [];
    foreach ($method->getParameters() as $param) {
        $args[] = ($class = $param->getClass()) ? $class->newInstance() : $argv[2] ?? null;
    }
    $method->invokeArgs(new $command, $args);
} catch (ErrorException $e) {
    handleErrorException($e);
} catch (Exception $e) {
    echo $e->getMessage(), PHP_EOL;
}
