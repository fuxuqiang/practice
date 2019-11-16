<?php

namespace app\auth;

class Admin
{
    public static function handle($id, $server)
    {
        if (($admin = mysql('admin')->cols('id', 'role_id')->where('id', $id)->get('src\Model', ['admin']))
            && (!($routeId = mysql('route')->where([
                'method' => $server['REQUEST_METHOD'],
                'uri' => ltrim($server['PATH_INFO'], '/')
            ])->val('id'))) || mysql('role_route')->where('role_id', $admin->role_id)->exists('route_id', $routeId)
        ) {
            return $admin;
        }
    }
}
