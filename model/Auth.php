<?php
namespace model;

use src\Mysql;

class Auth 
{
    public static function register($table, array $data)
    {
        $mysqli = Mysql::handler();
        $mysqli->begin_transaction();
        try {
            if (
                Mysql::query(
                    'SELECT `id` FROM `'.$table.'` WHERE `phone`=? FOR UPDATE',
                    'i',
                    [$data['phone']]
                )->num_rows
            ) {
                throw new \Exception('该手机号已注册过');
            }
            if ($table == 'user') {
                $token = uniqid();
                $data += ['api_token' => $token, 'token_expires' => date('Y-m-d H:i:s', strtotime('2 hour'))];
            }
            Mysql::table($table)->insert($data);
            $mysqli->commit();
            return $table == 'user' ? ['data' => $token, 'msg' => '注册成功'] : ['msg' => '添加成功'];
        } catch (\Exception $e) {
            $mysqli->rollback();
            return ['error' => $e->getMessage()];
        }
    }

    public static function getToken($table, $id)
    {
        $token = uniqid();
        Mysql::table($table)->id($id)
            ->update(['api_token' => $token, 'token_expires' => date('Y-m-d H:i:s', strtotime('2 hour'))]);
        return $token;
    }
}
