<?php

use Fuxuqiang\Framework\{Container, ResponseCode};

try {
    // 加载公共文件
    require __DIR__ . '/src/initError.php';
    // 处理跨域
    if ($cors = env('cors')) {
        header('Access-Control-Allow-Origin: ' . $cors);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type,Authorization');
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;
    }
    // 处理请求
    [$concrete, $method, $args] = (new \Src\Http(require __DIR__ . '/src/setting.php'))->handle($_SERVER, $_REQUEST);
    $response = (Container::newInstance($concrete))->$method(...$args);
// 异常处理
} catch (\Throwable $th) {
    http_response_code($th->getCode() ?: ResponseCode::InternalServerError->value);
    if (!$th instanceof RuntimeException) {
        if (env('debug')) {
            $response = ['error' => $th->getMessage(), 'trace' => $th->getTrace()];
        } else {
            logError($th);
        }
    } else {
        $response = error($th->getMessage());
    }
}

// 响应
if (!empty($response)) {
    header('Content-Type: application/json');
    echo is_string($response) ? $response : json_encode($response);
}
