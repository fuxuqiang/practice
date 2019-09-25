<?php
namespace app\auth;

class User
{
    public static function handle($id)
    {
        return mysql('user')->cols('id')->where('id', $id)->get('src\Model', ['user']);
    }
}
