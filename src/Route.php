<?php
namespace src;

class Route
{
    public static $routes = [];

    private $auth, $prefix;

    public function _add(array $routes)
    {
        foreach ($routes as $method => $group) {
            foreach ($group as $uri => $action) {
                $_group[$this->prefix ? $this->prefix.'/'.$uri : $uri] = $this->auth ? [$action, $this->auth] : $action;
            }
            self::$routes[$method] = array_merge(self::$routes[$method] ?? [], $_group);
        }
    }

    public function _auth(\src\Auth $auth)
    {
        $this->auth = $auth;
        return $this;
    }

    public function _prefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function __call($name, $args)
    {
        return call_user_func_array([$this, '_'.$name], $args);
    }

    public static function __callStatic($name, $args)
    {
        return call_user_func_array([new self, '_'.$name], $args);
    }
}
