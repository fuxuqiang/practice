#!/usr/bin/env php
<?php

require __DIR__ . '/src/env.php';
require __DIR__ . '/src/app.php';

try {
    // 执行
    $command = '\app\command\\' . ucfirst($argv[1]);
    (new $command)->handle($argv[2] ?? null);
} catch (ErrorException $e) {
    handleErrorException($e);
} catch (Exception $e) {
    echo $e->getMessage(), PHP_EOL;
}
