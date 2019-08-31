<?php
namespace app\auth;

class Admin
{
    public static function handle($token)
    {
        if ($admin = mysql()->query(
                'SELECT `id`,`role_id` FROM `admin` WHERE `api_token`=? AND `token_expires`>NOW()',
                [$token]
            )->fetch_object(\src\Model::class, ['admin'])) {
            if (($routeId = mysql('route')->where([
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'uri' => ltrim($_SERVER['PATH_INFO'], '/')
                ])->val('id')) && ! mysql('role_route')->where('role_id', $admin->role_id)
                    ->exists('route_id', $routeId)) {
                return false;
            }
            return $admin;
        }
        return false;
    }
}
