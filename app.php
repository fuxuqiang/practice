<?php

spl_autoload_register(function ($class) {
    strpos($class, 'PHPUnit') === 0 || require __DIR__.'/'.str_replace('\\', '/', $class).'.php';
});

require __DIR__.'/helpers.php';

if (! config('debug')) {
    ini_set('display_errors', 0);
    set_error_handler(function () {
        ob_start();
        debug_print_backtrace();
        logError(ob_get_clean());
    });
    register_shutdown_function(function () {
        ($error = error_get_last()) && logError($error['message']);
    });
}

\src\Container::bind('Redis', function () {
    $redis = new Redis;
    $config = config('redis');
    $redis->connect($config['host']);
    // $redis->auth($config['pwd']);
    return $redis;
});