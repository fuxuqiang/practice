#!/usr/bin/env php
<?php

require __DIR__ . '/src/config.php';
require __DIR__ . '/src/app.php';

try {
    // 执行
    $command = '\app\command\\' . ucfirst($argv[1]);
    (new $command)->handle();
} catch (ErrorException $e) {
    if (!config('debug')) {
        logError($e);
    } else {
        echo $e . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
