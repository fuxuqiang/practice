<?php
namespace app\controller;

class UserController
{
    /**
     * 修改名称
     */
    public function update()
    {
        $input = input(['name', 'capital']);
        if (isset($input['capital']) && !is_int($input['capital'] + 0)) {
            return ['error' => '资产须为数字'];
        }
        auth()->update($input);
        return ['msg' => '修改成功'];
    }

    /**
     * 交易
     */
    public function trade(int $type, $code, int $price, int $num, $date, $note = '')
    {
        $user = user();

        $total = $price * $num * 100;
        $fee = round($total/5000);
        $fee = $fee > 500 ? $fee : 500;
        $type = $type == 1 ? 1 : 2;

        $mysql = mysql();

        $mysqli = $mysql->mysqli;
        $mysqli->begin_transaction();
        
        try {
            $capital = $mysql->query(
                'SELECT `capital` FROM `user` WHERE `id`=? FOR UPDATE', 'i', [$user->id]
            )->fetch_row()[0];
            $positionNum = $mysql->query(
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
            mysql('trade')->insert([
                'user_id' => $user->id,
                'type' => $type,
                'code' => $code,
                'price' => $price,
                'num' => $num,
                'date' => $date,
                'note' => $note
            ]);
            mysql('position')->replace([
                'code' => $code,
                'user_id' => $user->id,
                'num' => ($positionNum ? $positionNum[0] : 0) + $num
            ]);
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
    public function getTrades(int $page = 1, int $per_page = 5)
    {
        $input = input();
        $cond = [];
        isset($input['type']) && $cond[] = ['type', '=', $input['type']];
        isset($input['code']) && $cond[] = ['code', '=', $input['code']];
        isset($input['start']) && $cond[] = ['date', '>', $input['start']];
        isset($input['end']) && $cond[] = ['date', '<', $input['end']];
        return [
            'data' => mysql('trade')->where($cond)->paginate($page, $per_page)
        ];
    }

    /**
     * 修改交易备注
     */
    public function updateTradeNote(int $id, $note)
    {
        mysql('trade')->where([['id', '=', $id], ['user_id', '=', auth()->id]])
            ->update(['note' => $note]);
        return ['msg' => '修改成功'];
    }
}
