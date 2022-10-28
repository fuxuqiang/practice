<?php

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set(env('timezone'));

if (env('debug')) {
    ini_set('display_errors', 'On');
    error_reporting(-1);
}

// 报错处理
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (error_reporting() & $errno) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
});
register_shutdown_function(function () {
    if ($error = error_get_last()) {
        handleThrowable(new ErrorException($error['message'], 0, 1, $error['file'], $error['line']));
    }
});

// 设置模型的数据库连接
\Fuxuqiang\Framework\Model\Model::setConnector(\Src\Mysql::getInstance());

return runtimePath('route.php');
