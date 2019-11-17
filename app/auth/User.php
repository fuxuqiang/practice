<?php
namespace app\auth;

class User
{
    public static function handle($payload)
    {
        $user = mysql('user')->cols('id', 'password')->where('id', $payload->sub)->get('src\Model', ['user']);
        return $user->password == $payload->jti ? $user : null;
    }
}
