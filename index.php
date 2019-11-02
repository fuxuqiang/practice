<?php

require __DIR__ . '/app.php';

if ($cors = config('common')['cors']) {
    header('Access-Control-Allow-Origin: ' . $cors);
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header('Access-Control-Allow-Headers: Authorization');
        exit;
    }
}

try {
    // 响应
    [$controller, $method, $args] = (new \src\Http)->handle($_SERVER, $_REQUEST);
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
