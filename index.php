<?php

// 允许跨域访问的域名
header('Access-Control-Allow-Origin: http://127.0.0.1');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Headers: Authorization');
    die;
}

// 加载助手函数
require __DIR__.'/helpers.php';

// 设置不同环境的报错提示
file_exists(__DIR__.'/.dev') || set_error_handler(function () {
    ob_start();
    debug_print_backtrace();
    file_put_contents(__DIR__.'/log/error.log', "\n".ob_get_clean(), FILE_APPEND | LOCK_EX);
    response(500);
});

// 注册自动加载
spl_autoload_register(function ($class) {
    require __DIR__.'/'.str_replace('\\', '/', $class).'.php';
});

// 解析路由
require __DIR__.'/route.php';
$pathInfo = isset($_SERVER['PATH_INFO']) ? ltrim($_SERVER['PATH_INFO'], '/') : '';
($route = \src\Route::$routes[$_SERVER['REQUEST_METHOD']][$pathInfo] ?? false) || response(404);
if (is_array($route)) {
    ($model = call_user_func([$route[1], 'handle'])) || response(401);
    auth($model);
    $route = $route[0];
}

// 定位控制器方法
$dispatch = explode('@', $route);
$controller = '\controller\\'.$dispatch[0].'Controller';
$method = new ReflectionMethod($controller, $dispatch[1]);
// 解析方法参数
$input = input();
$args = [];
foreach ($method->getParameters() as $param) {
    if (($paramName = $param->getName()) && isset($input[$paramName])) {
        $args[] = $input[$paramName];
    } else {
        response(400, file_exists(__DIR__.'/.dev') ? '缺少参数：'.$paramName : '');
    }
}
// 调用控制器方法
$response = $method->invokeArgs(new $controller, $args);

// 响应
if (!is_null($response)) {
    header('Content-Type: application/json');
    echo json_encode($response);
}
