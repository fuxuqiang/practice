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
                fn($val, $table, $col) => Mysql::table($table)->exists($col, $val),
                env('per_page')
            )
        );

        // 获取路由
        $route = $this->router->get($server['REQUEST_METHOD'], $request->uri);
        // 执行中间件
        foreach ($route['middlewares'] as $middleware) {
            (new $middleware)->handle($request);
        }
        // 解析方法参数
        $args = [];
        $input = $request->getData();
        foreach ((new \ReflectionMethod($route['class'], $route['method']))->getParameters() as $param) {
            $type = $param->getType();
            $args[] = match (true) {
                class_exists($type) => Container::get($type),
                isset($input[$paramName = $param->getName()]) && (!$type || ('is_'.$type)($input[$paramName])) => $input[$paramName],
                $param->isDefaultValueAvailable() => $param->getDefaultValue(),
                default => throw new ResponseException($paramName.'参数存在问题', ResponseException::BAD_REQUEST),
            };
        }

        return [$route['class'], $route['method'], $args];
    }
}
