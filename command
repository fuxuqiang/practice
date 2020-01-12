#!/usr/bin/env php
<?php

require __DIR__ . '/src/config.php';
require __DIR__ . '/src/app.php';

try {
    // 执行
    $command = '\app\command\\' . ucfirst($argv[1]);
    (new $command)->handle();
} catch (Exception $e) {
    echo $th->getMessage();
} catch (Error $e) {
    if (!config('debug')) {
        logError($th);
    } else {
        throw $th;
    }
}
