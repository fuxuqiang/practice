<?php

require __DIR__ . '/../vendor/autoload.php';

// 报错处理
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
register_shutdown_function(function () {
    if ($error = error_get_last()) {
        handleErrorException(new ErrorException($error['message'], 0, 1, $error['file'], $error['line']));
    }
});

// 设置模型的数据库连接
\Fuxuqiang\Framework\Model::setConnector(\Src\Mysql::getInstance());
