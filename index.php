<?php

// 加载助手函数
require __DIR__.'/helpers.php';

// 是否允许跨域
if ($cors = config('cors')) {
    header('Access-Control-Allow-Origin: '.$cors);
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header('Access-Control-Allow-Headers: Authorization');
        die;
    }
}

// 报错处理
if (!file_exists(__DIR__.'/.dev')) {
    ini_set('display_errors', 0);
    set_error_handler(function () {
        ob_start();
        debug_print_backtrace();
        logError(ob_get_clean());
    });
    register_shutdown_function(function () {
        ($error = error_get_last()) && $error['type'] == E_ERROR && logError($error['message']);
    });
}

// 注册自动加载
spl_autoload_register(function ($class) {
    require __DIR__.'/'.str_replace('\\', '/', $class).'.php';
});

// 定义模型的数据库连接
\src\Model::$connector = function ($model) {
    return mysql($model->table);
};

// 解析路由
require __DIR__.'/route.php';
$pathInfo = isset($_SERVER['PATH_INFO']) ? ltrim($_SERVER['PATH_INFO'], '/') : '';
($route = \src\Route::$routes[$_SERVER['REQUEST_METHOD']][$pathInfo] ?? false) || response(404);
if (is_array($route)) {
    ($model = $route[1]->handle()) || response(401);
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
