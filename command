#!/usr/bin/env php
<?php

require __DIR__ . '/src/app.php';

// 执行
$command = '\App\Command\\' . str_replace('/', '\\', $argv[1]);
$method = new ReflectionMethod($command, 'handle');
$args = [];
foreach ($method->getParameters() as $param) {
    if ($type = $param->getType()) {
        $class = $type->getName();
        $args[] = new $class;
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
