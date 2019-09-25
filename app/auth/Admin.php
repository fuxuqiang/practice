<?php
namespace app\auth;

class Admin
{
    public static function handle($id)
    {
        if (($admin = mysql('admin')->cols('id', 'role_id')->where('id', $id)->get('src\Model', ['admin']))
            && (!($routeId = mysql('route')->where([
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'uri' => ltrim($_SERVER['PATH_INFO'], '/')
                ])->val('id'))) || mysql('role_route')->where('role_id', $admin->role_id)->exists('route_id', $routeId)) {
            return $admin;
        }
    }
}
