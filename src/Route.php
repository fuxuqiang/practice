<?php
namespace src;

class Route
{
    public static $routes = [];

    private $auth, $prefix;

    private function add(array $routes)
    {
        foreach ($routes as $method => $group) {
            foreach ($group as $uri => $action) {
                $_group[$this->prefix ? $this->prefix.'/'.$uri : $uri] = $this->auth ?
                    [$action, $this->auth] : $action;
            }
            self::$routes[$method] = array_merge(self::$routes[$method] ?? [], $_group);
        }
    }

    private function auth($auth)
    {
        $this->auth = $auth;
        return $this;
    }

    private function prefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function resource($name, array $actions)
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

    public function __call($name, $args)
    {
        return $this->$name(...$args);
    }

    public static function __callStatic($name, $args)
    {
        return (new self)->$name(...$args);
    }
}
