<?php
namespace auth;

class Admin implements \src\Auth
{
    public static function handle($token)
    {
        if ($admin = mysql()->query(
                'SELECT `id`,`role_id` FROM `admin` WHERE `api_token`=? AND `token_expires`>NOW()',
                's',
                [$token]
            )->fetch_object(\src\Model::class)) {
            if (($route = mysql()->query(
                    'SELECT `id` FROM `route` WHERE `method`=? AND `uri`=?',
                    'ss',
                    [$_SERVER['REQUEST_METHOD'], ltrim($_SERVER['PATH_INFO'], '/')]
                )->fetch_row()) && !mysql()->query(
                    'SELECT `route_id` FROM `role_route` WHERE `role_id`=? AND `route_id`=?',
                    'ii',
                    [$admin->role_id, $route[0]]
                )->num_rows) {
                return false;
            }
            return $admin;
        }
        return false;
    }
}
