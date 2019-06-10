<?php
namespace auth;

class User
{
    public static function handle()
    {
        if (function_exists('getallheaders')) {
            $authHeader = getallheaders()['Authorization'] ?? false;
        } else {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? false;
        }
        if (
            $authHeader
            && $row = \src\Mysql::query('SELECT `id` FROM `user` WHERE `api_token`=? AND `token_expires`>NOW()', 's', [substr($authHeader, 7)])->fetch_row()
        ) {
            auth(new \model\User($row[0]));
        } else {
            response(401);
        }
    }
}
