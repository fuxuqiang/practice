<?php

namespace index\controllers;

use index\Mysql;
use index\models\User as UserModel;

class User
{
    /**
     * 设置密码
     */
    public function setPassword(UserModel $user)
    {
        $input = checkInput(['password']);
        if (!preg_match('/^(?!\d+$)(?![a-zA-Z]+$)[\dA-Za-z]{6,}$/', $input['password'])) {
            return ['error' => '密码长度至少为6位，由数字和字母组成'];
        }
        $user->update(['password' => password_hash($input['password'], PASSWORD_DEFAULT)]);
        return ['msg' => '修改成功'];
    }

    /**
     * 修改名称
     */
    public function update(UserModel $user)
    {
        $input = array_filter(checkInput(), function ($key) {
            return in_array($key, ['name', 'capital']);
        },  ARRAY_FILTER_USE_KEY);
        if (!is_int($input['capital'] + 0)) {
            return ['error' => '资产须为数字'];
        }
        $user->update($input);
        return ['msg' => '修改成功'];
    }

    /**
     * 交易
     */
    public function trade(UserModel $user)
    {
        checkInput(['type', 'code', 'price', 'num', 'date']);
        if (!is_int($_POST['price'] + 0)) {
            return ['error' => '价格须为数字'];
        }
        if (!is_int($_POST['num'] + 0)) {
            return ['error' => '数量须为数字'];
        }

        $total = $_POST['price'] * $_POST['num'] * 100;
        $fee = round($total/5000);
        $fee = $fee > 500 ? $fee : 500;
        $type = $_POST['type'] == 1 ? 1 : 2;

        $mysqli = Mysql::handler();
        $mysqli->begin_transaction();
        
        try {
            $capital = Mysql::query('SELECT `capital` FROM `user` WHERE `id`=? FOR UPDATE', 'i', [$user->id])->fetch_row()[0];
            $positionNum = Mysql::query(
                'SELECT `num` FROM `position` WHERE `code`=? AND `user_id`=? FOR UPDATE',
                'si',
                [$_POST['code'], $user->id]
            )->fetch_row();
            if ($type == 1) {
                $capital -= $total + $fee;
                if ($capital < 0) {
                    throw new \Exception('资金不足');
                }
                $num = $_POST['num'];
            } else {
                if (!$positionNum || $positionNum[0] < $_POST['num']) {
                    throw new \Exception('持仓不足');
                }
                $capital += $total - $fee - round($total/1000);
                $num = -$_POST['num'];
            }
            Mysql::query(
                'INSERT `trade` (`user_id`,`type`,`code`,`price`,`num`,`date`,`note`) VALUE (?,?,?,?,?,?,?)',
                'iisiiss',
                [$user->id, $type, $_POST['code'], $_POST['price'], $_POST['num'], $_POST['date'], $_POST['note'] ?? '']
            );
            Mysql::query(
                'REPLACE `position` (`code`,`user_id`,`num`) VALUE (?,?,?)',
                'sii',
                [$_POST['code'], $user->id, ($positionNum ? $positionNum[0] : 0) + $num]
            );
            $user->update(['capital' => $capital]);
            $mysqli->commit();
            return ['msg' => '交易成功'];
        } catch (\Exception $e) {
            $mysqli->rollback();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 交易记录
     */
    public function getTrades(UserModel $user)
    {
        return [
            'data' => Mysql::query('SELECT * FROM `trade` WHERE `user_id`=?', 'i', [$user->id])->fetch_all(MYSQLI_ASSOC)
        ];
    }
}
