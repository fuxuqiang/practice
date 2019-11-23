<?php

require __DIR__ . '/src/config.php';

// 处理跨域请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS' && $cors = config('cors')) {
    header('Access-Control-Allow-Origin: ' . $cors);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type,Authorization');
    exit;
}

require __DIR__ . '/src/app.php';

try {
    // 响应
    [$controller, $method, $args] = (new \src\Http)->handle($_SERVER, $_GET + $_POST);
    $response = $method->invokeArgs(new $controller, $args);
    if (!is_null($response)) {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
} catch (Throwable $th) {
    // 错误处理
    if ($th instanceof Exception) {
        http_response_code($th->getCode());
        echo $th->getMessage();
    } elseif (!config('debug')) {
        logError($th);
        http_response_code(500);
    } else {
        throw $th;
    }
}
