<?php

namespace src;

class Http
{
    public function __construct()
    {
        Container::bind('src\JWT', function () {
            $config = config('jwt');
            return new JWT($config['exp'], $config['key']);
        });
        require __DIR__ . '/../app/route.php';
    }

    /**
     * 处理请求
     */
    public function handle($server, $input)
    {
        // 匹配路由
        $pathInfo = isset($server['PATH_INFO']) ? ltrim($server['PATH_INFO'], '/') : '';
        if (!$route = Route::get($server['REQUEST_METHOD'], $pathInfo)) {
            throw new \Exception('', 404);
        }
        $user = null;

        // 验证token
        if (is_array($route)) {
            if (
                isset($server['HTTP_AUTHORIZATION'])
                && strpos($server['HTTP_AUTHORIZATION'], 'Bearer ') === 0
                && ($payload = Container::get('src\JWT')->decode(substr($server['HTTP_AUTHORIZATION'], 7)))
                && $user = $route[1]::handle($payload, $server)
            ) {
                $route = $route[0];
            } else {
                throw new \Exception('', 401);
            }
        }

        // 解析请求参数
        if (!$input) {
            if (isset($server['CONTENT_TYPE']) && $server['CONTENT_TYPE'] == 'application/json') {
                $input = json_decode(file_get_contents('php://input'), true);
            } else {
                parse_str(file_get_contents('php://input'), $input);
            }
        }
        // 实例化请求类
        $request = new Request($input, $user, function ($val, $table, $col) {
            return mysql($table)->exists($col, $val);
        }, config('per_page'));
        Container::instance('src\Request', $request);

        // 定位控制器方法
        $dispatch = explode('@', $route);
        $controller = '\app\controller\\' . $dispatch[0] . 'Controller';
        $method = new \ReflectionMethod($controller, $dispatch[1]);
        // 解析方法参数
        $args = [];
        foreach ($method->getParameters() as $param) {
            if ($class = $param->getClass()) {
                $args[] = Container::get($class->name);
            } elseif (($paramName = $param->getName()) && isset($input[$paramName])) {
                $args[] = $input[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \Exception(config('debug') ? '缺少参数：' . $paramName : '', 400);
            }
        }
        // 调用控制器方法
        return [$controller, $method, $args];
    }
}
