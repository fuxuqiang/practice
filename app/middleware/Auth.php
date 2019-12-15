<?php

namespace app\middleware;

use src\Mysql;
use vendor\{Request, JWT, Model};

class Auth
{
    public function handle(Request $request, JWT $jwt, $table)
    {
        $server = $request->server();
        if (($token = $request->token()) && $payload = $jwt->decode($token)) {
            if ($table == 'user') {
                $user = Mysql::table('user')->cols('id', 'password')->where('id', $payload->sub)->get(Model::class, ['user']);
            } elseif (
                $table == 'admin'
                && ($admin = Mysql::table('admin')->cols('id', 'role_id', 'password')->where('id', $payload->sub)->get(Model::class, ['admin']))
                && $admin->password == $payload->jti
                && (!($routeId = Mysql::table('route')->where(['method' => $server['REQUEST_METHOD'], 'uri' => ltrim($server['PATH_INFO'], '/')])->val('id')) || Mysql::table('role_route')->where('role_id', $admin->role_id)->exists('route_id', $routeId))
            ) {
                $user = $admin;
            }
        }
        if (isset($user)) {
            $request->setUser($user);
        } else {
            throw new \Exception('', 401);
        }
    }
}
