<?php

namespace src;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Http
     */
    protected $http;

    /**
     * 设置测试基境
     */
    protected function setUp(): void
    {
        $this->http || $this->http = new \src\Http;
    }

    /**
     * 调用测试请求
     */
    protected function request($requestMethod, $uri, $params, $token = null)
    {
        [$controller, $method, $args] = $this->http->handle([
            'REQUEST_METHOD' => $requestMethod,
            'PATH_INFO' => $uri,
            'HTTP_AUTHORIZATION' => $token ? 'Bearer ' . $token : null
        ], $params);
        Container::get($controller) || Container::instance($controller, new $controller);
        return $method->invokeArgs(Container::get($controller), $args);
    }

    /**
     * 根据方法名调用request方法
     */
    public function __call($name, $args)
    {
        return $this->request(strtoupper($name), ...$args);
    }
}
