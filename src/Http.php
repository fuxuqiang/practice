<?php

namespace Src;

use Fuxuqiang\Framework\{Container, JWT, Request, Route\Router, ResponseException};

class Http
{
    private $router;

    public function __construct($routeFile)
    {
        $this->router = new Router($routeFile);
        // 注册JWT实例
        Container::bind(JWT::class, function () {
            $config = env('jwt');
            return new JWT($config['exp'], $config['key']);
        });
    }

    /**
     * 处理请求
     */
    public function handle($server, $input)
    {
        // 实例化请求类
        Container::instance(
            Request::class,
            $request = new Request(
                $server,
                $input,
                fn($val, $table, $col) => \Src\Mysql::table($table)->exists($col, $val),
                env('per_page')
            )
        );

        // 解析方法参数
        $route = $this->router->get($server['REQUEST_METHOD'], $request->url());
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
                throw new ResponseException(env('debug') ? '缺少参数：' . $paramName : '', 400);
            }
        }

        return [$route['class'], $route['method'], $args];
    }
}
