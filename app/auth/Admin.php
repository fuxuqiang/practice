<?php

namespace app\auth;

class Admin
{
    public static function handle($payload, $server)
    {
        if (
            ($admin = mysql('admin')->cols('id', 'role_id', 'password')->where('id', $payload->sub)->get('src\Model', ['admin']))
            && $admin->password == $payload->jti
            && (!($routeId = mysql('route')
                ->where(['method' => $server['REQUEST_METHOD'], 'uri' => ltrim($server['PATH_INFO'], '/')])->val('id')))
            || mysql('role_route')->where('role_id', $admin->role_id)->exists('route_id', $routeId)
        ) {
            return $admin;
        }
    }
}
