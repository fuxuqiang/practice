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
     * @var string
     */
    protected $token;

    /**
     * 设置测试基境
     */
    public static function setUpBeforeClass(): void
    {
        if (!self::$http) {
            require __DIR__ . '/env.php';
            self::$http = new Http;
        }
        Mysql::begin();
    }

    /**
     * 调用测试请求
     */
    protected function request($requestMethod, $uri, $params = [], $token = null)
    {
        $token = $token ?: $this->token;
        try {
            [$controller, $method, $args] = self::$http->handle([
                'REQUEST_METHOD' => $requestMethod,
                'PATH_INFO' => $uri,
                'HTTP_AUTHORIZATION' => $token ? 'Bearer ' . $token : null
            ], $params);
            Container::get($controller) || Container::instance($controller, new $controller);
            $response = Container::get($controller)->$method(...$args);
            $status = 200;
        } catch (\Exception $e) {
            $response = error($e->getMessage());
            $status = $e->getCode();
        }
        $this->token = null;
        return new \vendor\TestResponse($response, $status);
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

    /**
     * 断言数据库中数据是否存在
     */
    public function assertDatabaseHas($table, $data)
    {
        return $this->assertTrue(Mysql::table($table)->where($data)->count() > 0);
    }
}
