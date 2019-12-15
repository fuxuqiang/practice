<?php
namespace app\controller;

use src\Mysql;
use vendor\Request;
use app\model\Region;

class OrderController
{
    /**
     * 下单
     */
    public function add($address_id, Request $request)
    {
        // 参数验证
        $request->validate(['skus' => 'array']);
        $userId = $request->user()->id;
        if (! $address = Mysql::table('address')->cols('code', 'address')
            ->where(['user_id' => $userId, 'id' => $address_id])->get()) {
            return ['error' => '不存在的地址'];
        }
        // 格式化skus参数
        $skuIds = array_column($request->skus, 'id');
        $skus = array_column($request->skus, 'num', 'id');
        // 事务
        Mysql::begin();
        try {
            // sku详情
            $skuInfos = Mysql::table('sku')->where('num', '>', 0)->whereIn('id', $skuIds)
                ->lock()->all('id', 'name', 'price', 'num');
            if (!$skuInfos || count($skuIds) > count($skuInfos)) {
                throw new \Exception('商品不存在或已售罄');
            }
            // 订单表
            $id = Mysql::table('order')->insert([
                'user_id' => $userId,
                'region_code' => $address->code,
                'address' => $address->address
            ]);
            // 订单明细表
            foreach ($skuInfos as $skuInfo) {
                $num = $skus[$skuInfo['id']];
                if ($skuInfo['num'] < $num) {
                    throw new \Exception($skuInfo['name'].'库存不足');
                }
                $orderSkus[] = [$id, $skuInfo['id'], $skuInfo['price'], $num, $skuInfo['name']];
            }
            Mysql::table('order_sku')->cols('order_id', 'sku_id', 'price', 'num', 'name')
                ->insert($orderSkus);
            // 减库存
            foreach ($skus as $id => $num) {
                Mysql::query('UPDATE `sku` SET `num`=`num`-? WHERE `id`=?', [$num, $id]);
            }
            Mysql::commit();
            return ['msg' => '下单成功'];
        } catch (\Exception $e) {
            Mysql::rollback();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 订单详情
     */
    public function info($id)
    {
        $order = Mysql::table('order')->where('id', $id)->get();
        return [
            'id' => $order->id,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'address' => implode(
                    '',
                    Mysql::table('region')->whereIn('code', Region::getAllCode($order->region_code))->col('name')
                ).$order->address,
            'skus' => Mysql::table('order_sku')->where('order_id', $id)->all('sku_id', 'name', 'price', 'num')
        ];
    }
}
