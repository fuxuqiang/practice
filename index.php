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
file_exists(__DIR__.'/.dev') || set_error_handler(function () { response(500); });

// 注册自动加载
spl_autoload_register(function ($class) {
    require __DIR__.'/'.str_replace('\\', '/', $class).'.php';
});

// 加载路由定义
require __DIR__.'/route.php';
// 匹配路由
$pathInfo = isset($_SERVER['PATH_INFO']) ? ltrim($_SERVER['PATH_INFO'], '/') : '';
($route = \src\Route::$routes[$_SERVER['REQUEST_METHOD']][$pathInfo] ?? false) || response(404);
// 判断路由是否需要认证
if (is_array($route)) {
    call_user_func([$route[1], 'handle']);
    $route = $route[0];
}
// 分发到控制器
$dispatch = explode('@', $route);
$controller = '\\controller\\'.$dispatch[0];
$response = call_user_func([new $controller, $dispatch[1]]);
// 响应
if (!is_null($response)) {
    header('Content-Type: application/json');
    echo json_encode($response);
}
