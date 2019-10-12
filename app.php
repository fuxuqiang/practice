<?php

spl_autoload_register(function ($class) {
    require __DIR__.'/'.str_replace('\\', '/', $class).'.php';
});

require __DIR__.'/helpers.php';

\src\Container::bind('Redis', function () {
    $redis = new Redis;
    $config = config('redis');
    $redis->connect($config['host']);
    // $redis->auth($config['pwd']);
    return $redis;
});