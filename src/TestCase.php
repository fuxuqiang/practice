<?php

namespace src;

use vendor\Container;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Http
     */
    protected static $http;

    /**
     * 设置测试基境
     */
    public static function setUpBeforeClass(): void
    {
        if (!self::$http) {
            require __DIR__ . '/config.php';
            self::$http = new Http;
        }
        Mysql::begin();
    }

    /**
     * 调用测试请求
     */
    protected function request($requestMethod, $uri, $params = [], $token = null)
    {
        [$controller, $method, $args] = self::$http->handle([
            'REQUEST_METHOD' => $requestMethod,
            'PATH_INFO' => $uri,
            'HTTP_AUTHORIZATION' => $token ? 'Bearer ' . $token : null
        ], $params);
        Container::get($controller) || Container::instance($controller, new $controller);
        try {
            return $method->invokeArgs(Container::get($controller), $args);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 根据方法名调用request方法
     */
    public function __call($name, $args)
    {
        return $this->request(strtoupper($name), ...$args);
    }

    /**
     * 清理测试基镜
     */
    public static function tearDownAfterClass(): void
    {
        Mysql::rollback();
    }
}
