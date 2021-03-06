<?php

require __DIR__ . '/src/env.php';

// 处理跨域
if ($cors = env('cors')) {
    header('Access-Control-Allow-Origin: ' . $cors);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type,Authorization');
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit;
    }
}

require __DIR__ . '/src/app.php';

// 处理请求
try {
    [$concrete, $method, $args] = (new \Src\Http)->handle($_SERVER, $_GET + $_POST);
    $response = (\Fuxuqiang\Framework\Container::newInstance($concrete))->$method(...$args);
// 错误处理
} catch (ErrorException $e) {
    handleErrorException($e);
// 异常处理
} catch (Exception $e) {
    http_response_code($e->getCode());
    ($msg = $e->getMessage()) && $response = error($msg);
}

// 响应
if (isset($response)) {
    header('Content-Type: application/json');
    echo is_string($response) ? $response : json_encode($response);
}

