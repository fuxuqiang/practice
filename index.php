<?php

require __DIR__ . '/src/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS' && $cors = config('cors')) {
    header('Access-Control-Allow-Origin: ' . $cors);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type,Authorization');
    exit;
}

require __DIR__ . '/src/app.php';

try {
    // 调用控制器方法
    [$controller, $method, $args] = (new \src\Http)->handle($_SERVER, $_GET + $_POST);
    $response = $method->invokeArgs(new $controller, $args);
} catch (Throwable $th) {
    // 错误处理
    if ($th instanceof Exception) {
        http_response_code($th->getCode());
        $response = ['error' => $th->getMessage()];
    } elseif (!config('debug')) {
        logError($th);
        http_response_code(500);
    } else {
        throw $th;
    }
}

if (!is_null($response)) {
    header('Content-Type: application/json');
    echo json_encode($response);
}
