<?php

require __DIR__ . '/src/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS' && $cors = config('cors')) {
    header('Access-Control-Allow-Origin: ' . $cors);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type,Authorization');
    exit;
}

require __DIR__ . '/src/app.php';

// 处理请求
try {
    [$controller, $method, $args] = (new \src\Http)->handle($_SERVER, $_GET + $_POST);
    $response = $method->invokeArgs(new $controller, $args);
// 异常处理
} catch (Exception $e) {
    http_response_code($e->getCode());
    $response = error($e->getMessage());
// 错误处理
} catch (Error $e) {
    http_response_code(500);
    if (!config('debug')) {
        logError($e);
    } else {
        echo $e;
    }
// 响应
} finally {
    if (isset($response) && !is_null($response)) {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
