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
        isset($input['capital']) && validate(['capital' => 'posInt']);
        auth()->update($input);
        return ['msg' => '修改成功'];
    }

    /**
     * 交易
     */
    public function trade($code, $price, $num, $date, $note = '')
    {
        validate(['price' => 'int|min:1', 'num' => 'int|nq:0']);

        $user = auth();

        $total = $price * $num * 100;
        $fee = round($total/5000);
        $fee = $fee > 500 ? $fee : 500;

        $mysqli = mysql()->handler();
        $mysqli->begin_transaction();
        try {
            $capital = mysql('user')->where('id', $user->id)->val('capital');
            $positionNum = mysql('position')
                ->where(['code' => $code, 'user_id' => $user->id])->val('num');
            $capital -= $total + $fee;
            if ($num > 0) {
                if ($capital < 0) {
                    throw new \Exception('资金不足');
                }
            } else {
                if ($positionNum + $num < 0) {
                    throw new \Exception('持仓不足');
                }
                $capital -= round($total/1000);
            }
            mysql('trade')->insert([
                'user_id' => $user->id,
                'code' => $code,
                'price' => $price,
                'num' => $num,
                'date' => $date,
                'note' => $note
            ]);
            mysql('position')->replace([
                'code' => $code,
                'user_id' => $user->id,
                'num' => ($positionNum ?: 0) + $num
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
    public function getTrades()
    {
        $input = input();
        $cond = [];
        isset($input['type']) && $cond['type'] = $input['type'];
        isset($input['code']) && $cond['code'] = $input['code'];
        isset($input['start']) && $cond[] = ['date', '>', $input['start']];
        isset($input['end']) && $cond[] = ['date', '<', $input['end']];
        return [
            'data' => mysql('trade')->where($cond)->paginate(...pageParams())
        ];
    }

    /**
     * 修改交易备注
     */
    public function updateTradeNote($id, $note)
    {
        mysql('trade')->where(['id' => $id, 'user_id' => auth()->id])->update(['note' => $note]);
        return ['msg' => '修改成功'];
    }
}
