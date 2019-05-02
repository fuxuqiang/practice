<?php

namespace index\models;

use index\Mysql;

class User
{
    public $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function add($phone)
    {
        $mysqli = Mysql::handler();
        $mysqli->begin_transaction();
        try {
            if (
                Mysql::query('SELECT `id` FROM `user` WHERE `phone`=? FOR UPDATE', 's', [$phone])->num_rows
            ) {
                throw new \Exception('该手机号已注册过');
            }
            $token = uniqid();
            Mysql::query(
                'INSERT `user` (`phone`,`api_token`,`token_expires`) VALUE (?,?,UNIX_TIMESTAMP()+864000)',
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

    public function update(array $data)
    {
        $sql = 'UPDATE `user` SET ';
        $types = '';
        foreach ($data as $key => $value) {
            $types .= 's';
            $sql .= '`'.$key.'`=?,';
        }
        $sql = rtrim($sql, ',').' WHERE `id`='.$this->id;
        Mysql::query($sql, $types, $data);
    }
}
