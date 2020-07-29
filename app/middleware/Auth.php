<?php

namespace app\middleware;

use src\Mysql;
use vendor\{Request, JWT, Model};

class Auth
{
    public function handle(Request $request, JWT $jwt, $table)
    {
        $server = $request->server();
        if (
            ($token = $request->token()) && ($payload = $jwt->decode($token))
            && (
                (
                    $table == 'user'
                    && ($user = Mysql::table('user')->cols('id', 'password')
                        ->where(['id' => $payload->sub, 'is_forbidden' => 0])->get(Model::class, ['user']))
                    || $table == 'admin'
                    && ($user = Mysql::table('admin')->cols('id', 'role_id', 'password')
                        ->where('id', $payload->sub)->get(Model::class, ['admin']))
                )
                && $table . $user->password == $payload->jti
                && (
                    !($routeId = Mysql::table('route')
                        ->where(['method' => $server['REQUEST_METHOD'], 'uri' => ltrim($request->uri(), 'admin/')])->val('id'))
                    || Mysql::table('role_route')->where('role_id', $user->role_id)->exists('route_id', $routeId)
                )
            )
        ) {
            $request->setUser($user);
        } else {
            throw new \Exception('', 401);
        }
    }
}
