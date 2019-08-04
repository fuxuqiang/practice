<?php
namespace app\model;

class Auth 
{
    /**
     * 注册手机
     */
    public static function registerPhone($table, $phone, callable $callback, $msg)
    {
        $mysqli = mysql()->mysqli;
        $mysqli->begin_transaction();
        try {
            if (
                mysql()->query(
                    'SELECT `id` FROM `'.$table.'` WHERE `phone`=? FOR UPDATE', 'i', [$phone]
                )->num_rows
            ) {
                throw new \Exception('该手机号已注册过');
            }
            $callback();
            $mysqli->commit();
            return $msg;
        } catch (\Exception $e) {
            $mysqli->rollback();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取token
     */
    public static function getToken($table, $id)
    {
        $token = uniqid();
        mysql($table)->where('id', $id)
            ->update(['api_token' => $token, 'token_expires' => timestamp('2 hour')]);
        return $token;
    }
    
    /**
     * 添加用户
     */
    public static function addUser($phone)
    {
        $token = uniqid();
        return self::registerPhone('user', $phone, function () use ($token, $phone) {
            mysql('user')->insert([
                'phone' => $phone,
                'api_token' => $token,
                'token_expires' => timestamp('2 hour')
            ]);
        }, ['data' => $token, 'msg' => '注册成功']);
    }
}
