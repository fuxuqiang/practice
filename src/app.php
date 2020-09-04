<?php

// 自动加载类文件
spl_autoload_register(function ($class) {
    strpos($class, 'PHPUnit') === 0 || require __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
});

// 加载助手函数
require __DIR__ . '/helpers.php';
require __DIR__ . '/../app/helpers.php';

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
register_shutdown_function(function () {
    if ($error = error_get_last()) {
        handleErrorException(new ErrorException($error['message'], 0, 1, $error['file'], $error['line']));
    }
});

// 设置模型的数据库连接
\vendor\Model::setConnector(\src\Mysql::getInstance());
