<?php
namespace controller;

use src\Mysql;

class UserController
{
    /**
     * 修改名称
     */
    public function update()
    {
        $input = array_filter(input(), function ($key) {
            return in_array($key, ['name', 'capital']);
        },  ARRAY_FILTER_USE_KEY);
        if (isset($input['capital']) && !is_int($input['capital'] + 0)) {
            return ['error' => '资产须为数字'];
        }
        auth()->update($input);
        return ['msg' => '修改成功'];
    }

    /**
     * 交易
     */
    public function trade(int $type, $code, int $price, int $num, $date)
    {
        $user = user();

        $total = $price * $num * 100;
        $fee = round($total/5000);
        $fee = $fee > 500 ? $fee : 500;
        $type = $type == 1 ? 1 : 2;

        $mysqli = Mysql::handler();
        $mysqli->begin_transaction();
        
        try {
            $capital = Mysql::query(
                'SELECT `capital` FROM `user` WHERE `id`=? FOR UPDATE', 'i', [$user->id]
            )->fetch_row()[0];
            $positionNum = Mysql::query(
                'SELECT `num` FROM `position` WHERE `code`=? AND `user_id`=? FOR UPDATE',
                'si',
                [$code, $user->id]
            )->fetch_row();
            if ($type == 1) {
                $capital -= $total + $fee;
                if ($capital < 0) {
                    throw new \Exception('资金不足');
                }
                $num = $num;
            } else {
                if (!$positionNum || $positionNum[0] < $num) {
                    throw new \Exception('持仓不足');
                }
                $capital += $total - $fee - round($total/1000);
                $num = -$num;
            }
            Mysql::query(
                'INSERT `trade` (`user_id`,`type`,`code`,`price`,`num`,`date`,`note`) VALUE (?,?,?,?,?,?,?)',
                'iisiiss',
                [$user->id, $type, $code, $price, $num, $date, $note ?? '']
            );
            Mysql::query(
                'REPLACE `position` (`code`,`user_id`,`num`) VALUE (?,?,?)',
                'sii',
                [$code, $user->id, ($positionNum ? $positionNum[0] : 0) + $num]
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
    public function getTrades()
    {
        return [
            'data' => Mysql::query('SELECT * FROM `trade` WHERE `user_id`=?', 'i', [auth()->id])->fetch_all(MYSQLI_ASSOC)
        ];
    }

    /**
     * 修改交易备注
     */
    public function updateTradeNote(int $id, $note)
    {
        Mysql::query('UPDATE `trade` SET `note`=? WHERE `id`=? AND `user_id`=?', 'sii', [$note, $id, auth()->id]);
        return ['msg' => '修改成功'];
    }
}
