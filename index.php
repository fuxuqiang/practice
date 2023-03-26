<?php

use Fuxuqiang\Framework\{Container, ResponseException};

try {
    // 加载公共脚本，获取路由文件
    $routeFile = require __DIR__ . '/src/app.php';
    // 处理跨域
    if ($cors = env('cors')) {
        header('Access-Control-Allow-Origin: ' . $cors);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type,Authorization');
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;
    }
    // 处理请求
    [$concrete, $method, $args] = (new \Src\Http($routeFile))->handle($_SERVER, $_REQUEST);
    $response = (Container::newInstance($concrete))->$method(...$args);
// 异常响应
} catch (ResponseException $e) {
    http_response_code($e->getCode());
    $response = ($msg = $e->getMessage()) ? error($msg) : '';
// 其他异常处理
} catch (\Throwable $th) {
    http_response_code(ResponseException::INTERNAL_SERVER_ERROR);
    handleThrowable($th);
}

// 响应
if (!empty($response)) {
    header('Content-Type: application/json');
    echo is_string($response) ? $response : json_encode($response);
}
