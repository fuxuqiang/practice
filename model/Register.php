<?php
namespace model;

use src\Mysql;

class Register 
{
    public static function handle($table, $phone)
    {
        $mysqli = Mysql::handler();
        $mysqli->begin_transaction();
        try {
            if (
                Mysql::query('SELECT `id` FROM `'.$table.'` WHERE `phone`=? FOR UPDATE', 'i', [$phone])->num_rows
            ) {
                throw new \Exception('该手机号已注册过');
            }
            $token = uniqid();
            Mysql::query(
                'INSERT `'.$table.'` (`phone`,`api_token`,`token_expires`) VALUE (?,?,TIMESTAMPADD(HOUR,1,NOW()))',
                'is',
                [$phone, $token]
            );
            $mysqli->commit();
            return ['data' => $token, 'msg' => '注册成功'];
        } catch (\Exception $e) {
            $mysqli->rollback();
            return ['error' => $e->getMessage()];
        }
    }
}
