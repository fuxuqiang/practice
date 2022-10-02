<?php

namespace Src;

use Fuxuqiang\Framework\{Container, JWT, Request, Route\Router};

class Http
{
    public function __construct()
    {
        Container::bind(JWT::class, function () {
            $config = env('jwt');
            return new JWT($config['exp'], $config['key']);
        });
    }

    /**
     * 处理请求
     */
    public function handle($server, $input, $routeFile)
    {
        // 实例化请求类
        $request = new Request($server, $input, function ($val, $table, $col) {
            return \Src\Mysql::table($table)->exists($col, $val);
        }, env('per_page'));

        $route = (new Router($routeFile))->get($server['REQUEST_METHOD'], $request->uri());

        Container::instance(Request::class, $request);

        // 解析方法参数
        $args = [];
        $input = $request->get();
        foreach ((new \ReflectionMethod($route['class'], $route['method']))->getParameters() as $param) {
            if ($class = $param->getType()) {
                $args[] = Container::get($class);
            } elseif (($paramName = $param->getName()) && isset($input[$paramName])) {
                $args[] = $input[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \Exception(env('debug') ? '缺少参数：' . $paramName : '', 400);
            }
        }

        return [$route['class'], $route['method'], $args];
    }
}
