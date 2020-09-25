<?php

namespace Src;

use Fuxuqiang\Framework\{Container, TestResponse};

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
    }

    /**
     * 调用测试请求
     */
    protected function request($requestMethod, $uri, $params = [], $token = null)
    {
        $token = $token ?: $this->token;
        try {
            [$concrete, $method, $args] = self::$http->handle([
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $uri,
                'HTTP_AUTHORIZATION' => $token ? 'Bearer ' . $token : null
            ], $params);
            if (!$controller = Container::get($concrete)) {
                $controller = Container::newInstance($concrete);
                Container::instance($concrete, $controller);
            }
            $response = $controller->$method(...$args);
            $status = 200;
        } catch (\Exception $e) {
            $response = error($e->getMessage());
            $status = $e->getCode();
        }
        $this->token = null;
        return new TestResponse($response, $status);
    }

    /**
     * 根据方法名调用request方法
     */
    public function __call($name, $args)
    {
        return $this->request(strtoupper($name), ...$args);
    }

    /**
     * 断言数据库中数据是否存在
     */
    public function assertDatabaseHas($table, $data)
    {
        return $this->assertTrue(Mysql::table($table)->where($data)->count() > 0);
    }
}
