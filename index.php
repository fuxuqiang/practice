<?php

use index\models\User;

// 允许跨域访问的域名
header('Access-Control-Allow-Origin: http://127.0.0.1');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Headers: Authorization');
    die;
}

// 加载助手函数
require __DIR__.'/helpers.php';
// 设置报错提示
file_exists(__DIR__.'/.dev') || set_error_handler(function () { response(500); });

// 路由
$routes = require __DIR__.'/route.php';
array_walk_recursive($routes, function (&$val) {
    $val = '/index/controllers/'.$val;
});

// PATH_INFO
$pathInfo = isset($_SERVER['PATH_INFO']) ? ltrim($_SERVER['PATH_INFO'], '/') : '';

// 注册自动加载
spl_autoload_register(function ($class) {
    require __DIR__.strtr($class, ['\\' => '/', 'index' => '']).'.php';
});

// 判断路由是否需要认证
foreach ($routes as $key => $value) {
    $route = $value[$_SERVER['REQUEST_METHOD']][$pathInfo] ?? false;
    if ($route) {
        if ($key == 'auth') {
            if (function_exists('getallheaders')) {
                $authHeader = getallheaders()['Authorization'] ?? false;
            } else {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? false;
            }
            if (
                $authHeader
                && $row = \index\Mysql::query('SELECT `id` FROM `user` WHERE `api_token`=? AND `token_expires`>UNIX_TIMESTAMP()', 's', [substr($authHeader, 7)])->fetch_row()
            ) {
                $user = new User($row[0]);
            } else {
                response(401);
            }
        }
        break;
    }
}

$route || response(404);

$pos = strrpos($route, '/');
$controller = str_replace('/', '\\', substr($route, 0, $pos));
$object = new $controller;
$method = new ReflectionMethod($object, substr($route, $pos + 1));

if (isset($user) && ($params = $method->getParameters()) && $params[0]->getClass()->name == User::class) {
    $response = $method->invokeArgs($object, [$user]);
} else {
    $response = $method->invoke($object);
}

if (!is_null($response)) {
    header('Content-Type: application/json');
    json($response);
}
