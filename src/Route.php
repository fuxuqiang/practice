<?php
namespace src;

class Route
{
    private static $routes = [];

    private $auth, $prefix;

    /**
     * 添加路由
     */
    private function add(array $routes)
    {
        foreach ($routes as $method => $group) {
            foreach ($group as $uri => $action) {
                $_group[$this->prefix ? rtrim($this->prefix.'/'.$uri, '/') : $uri] = $this->auth ?
                    [$action, $this->auth] : $action;
            }
            self::$routes[$method] = array_merge(self::$routes[$method] ?? [], $_group);
        }
    }

    /**
     * 设置路由权限验证
     */
    protected function auth($auth)
    {
        $this->auth = $auth;
        return $this;
    }

    /**
     * 设置路由前缀
     */
    protected function prefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 设置资源路由
     */
    protected function resource(string $name, array $actions)
    {
        $actions = array_intersect($actions, ['add', 'update', 'del', 'list']);
        $methods = ['add' => 'POST', 'update' => 'PUT', 'del' => 'DELETE', 'list' => 'GET'];
        foreach ($actions as $action) {
            $this->add([
                $methods[$action] => [
                    $name.($action == 'list' ? 's' : '') => ucfirst($name).'@'.$action
                ]
            ]);
        }
    }

    /**
     * 获取路由
     */
    public static function get($method, $uri)
    {
        return self::$routes[$method][$uri] ?? null;
    }

    /**
     * 调用类方法
     */
    public function __call($name, $args)
    {
        return $this->$name(...$args);
    }

    /**
     * 静态调用类方法
     */
    public static function __callStatic($name, $args)
    {
        return (new self)->$name(...$args);
    }
}
