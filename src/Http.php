<?php

namespace src;

use vendor\{Container, JWT, Request, Route};

class Http
{
    public function __construct()
    {
        Container::bind('vendor\JWT', function () {
            $config = env('jwt');
            return new JWT($config['exp'], $config['key']);
        });
        require __DIR__ . '/../app/route.php';
    }

    /**
     * 处理请求
     */
    public function handle($server, $input)
    {
        // 实例化请求类
        $request = new Request($server, $input, function ($val, $table, $col) {
            return \src\Mysql::table($table)->exists($col, $val);
        }, env('per_page'));

        // 匹配路由
        $route = Route::get($server['REQUEST_METHOD'], $request->uri());
        if (is_array($route)) {
            foreach ($route[1] as $key => $val) {
                $jwt = Container::get('vendor\JWT');
                if (is_array($val)) {
                    (new $key)->handle($request, $jwt, ...$val);
                } else {
                    (new $val)->handle($request, $jwt);
                }
            }
            $route = $route[0];
        }

        Container::instance('vendor\Request', $request);

        // 定位控制器方法
        $dispatch = explode('@', $route);
        $controller = '\app\controller\\' . $dispatch[0] . 'Controller';
        // 解析方法参数
        $args = [];
        $input = $request->get();
        foreach ((new \ReflectionMethod($controller, $dispatch[1]))->getParameters() as $param) {
            if ($class = $param->getClass()) {
                $args[] = Container::get($class->name);
            } elseif (($paramName = $param->getName()) && isset($input[$paramName])) {
                $args[] = $input[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \Exception(env('debug') ? '缺少参数：' . $paramName : '', 400);
            }
        }

        return [$controller, $dispatch[1], $args];
    }
}
