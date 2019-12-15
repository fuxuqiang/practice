<?php
namespace app\controller;

use vendor\Mysql;
use vendor\Request;

class UserController
{
    /**
     * 修改名称
     */
    public function update(Request $request)
    {
        $input = $request->get('name', 'capital');
        isset($input['capital']) && $request->validate(['capital' => 'posInt']);
        $request->user()->update($input);
        return ['msg' => '修改成功'];
    }

    /**
     * 交易
     */
    public function trade($code, $price, $num, $date, $note = '', Request $request)
    {
        $request->validate(['price' => 'int|min:1', 'num' => 'int|nq:0']);

        $user = $request->user();

        $total = $price * $num * 100;
        $fee = round($total/5000);
        $fee = $fee > 500 ? $fee : 500;

        Mysql::begin();
        try {
            $capital = Mysql::table('user')->where('id', $user->id)->val('capital');
            $positionNum = Mysql::table('position')
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
            Mysql::table('trade')->insert([
                'user_id' => $user->id,
                'code' => $code,
                'price' => $price,
                'num' => $num,
                'date' => $date,
                'note' => $note
            ]);
            Mysql::table('position')->replace([
                'code' => $code,
                'user_id' => $user->id,
                'num' => ($positionNum ?: 0) + $num
            ]);
            $user->update(['capital' => $capital]);
            Mysql::commit();
            return ['msg' => '交易成功'];
        } catch (\Exception $e) {
            Mysql::rollback();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 交易记录
     */
    public function getTrades(Request $request)
    {
        $input = $request->get();
        $cond = [];
        isset($input['code']) && $cond['code'] = $input['code'];
        isset($input['start']) && $cond[] = ['date', '>', $input['start']];
        isset($input['end']) && $cond[] = ['date', '<', $input['end']];
        return [
            'data' => Mysql::table('trade')->where($cond)->paginate(...$request->pageParams())
        ];
    }

    /**
     * 修改交易备注
     */
    public function updateTradeNote($id, $note, Request $request)
    {
        Mysql::table('trade')->where(['id' => $id, 'user_id' => $request->user()->id])
            ->update(['note' => $note]);
        return ['msg' => '修改成功'];
    }
}
