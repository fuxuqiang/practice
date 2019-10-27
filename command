#!/usr/bin/env php
<?php

require __DIR__.'/app.php';

try {
    $command = '\app\command\\'.ucfirst($argv[1]);
    (new $command)->handle();    
} catch (\Throwable $th) {
    if ($th instanceof Exception) {
        echo $th->getMessage();
    } elseif (! config('debug')) {
        logError($th);
    } else {
        throw $th;
    }
}
