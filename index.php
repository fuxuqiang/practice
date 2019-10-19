<?php

use src\Container;

// 加载公共文件
require __DIR__.'/app.php';
require __DIR__.'/app/helpers.php';

// 是否允许跨域
$config = config('common');
if ($cors = $config['cors']) {
    header('Access-Control-Allow-Origin: '.$cors);
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header('Access-Control-Allow-Headers: Authorization');
        exit;
    }
}

// 报错处理
if (! $config['debug']) {
    ini_set('display_errors', 0);
    set_error_handler(function () {
        ob_start();
        debug_print_backtrace();
        logError(ob_get_clean());
    });
    register_shutdown_function(function () {
        ($error = error_get_last()) && logError($error['message']);
    });
}

// 注册JWT类
Container::bind('src\JWT', function () {
    $config = config('jwt');
    return new \src\JWT($config['id'], $config['exp']);
});

// 定义模型的数据库连接
\src\Model::setConnector(function ($model) {
    return mysql($model->getTable())->where('id', $model->id);
});

// 获取路由
require __DIR__.'/app/route.php';
$pathInfo = isset($_SERVER['PATH_INFO']) ? ltrim($_SERVER['PATH_INFO'], '/') : '';
$route = \src\Route::get($_SERVER['REQUEST_METHOD'], $pathInfo) ?: response(404);
$user = null;

try {
    // 验证token
    if (is_array($route)) {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])
            && strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0
            && ($id = Container::get('src\JWT')->decode(substr($_SERVER['HTTP_AUTHORIZATION'], 7)))
            && $user = $route[1]::handle($id)) {
            $route = $route[0];
        } else {
            response(401);
        }
    }

    // 实例化请求类
    $request = new \src\Request($user, function ($val, $table, $col) {
        return mysql($table)->exists($col, $val);
    }, $config['per_page']);
    Container::instance('src\Request', $request);

    // 定位控制器方法
    $dispatch = explode('@', $route);
    $controller = '\app\controller\\'.$dispatch[0].'Controller';
    $method = new ReflectionMethod($controller, $dispatch[1]);
    // 解析方法参数
    $input = $request->get();
    $args = [];
    foreach ($method->getParameters() as $param) {
        if ($class = $param->getClass()) {
            $args[] = Container::get($class->name);
        } elseif (($paramName = $param->getName()) && isset($input[$paramName])) {
            $args[] = $input[$paramName];
        } elseif ($param->isDefaultValueAvailable()) {
            $args[] = $param->getDefaultValue();
        } else {
            response(400, file_exists(__DIR__.'/.dev') ? '缺少参数：'.$paramName : '');
        }
    }
    // 调用控制器方法
    $response = $method->invokeArgs(new $controller, $args);

} catch (Exception $e) {
    response(400, $e->getMessage());
}

// 响应
if (!is_null($response)) {
    header('Content-Type: application/json');
    echo json_encode($response);
}
