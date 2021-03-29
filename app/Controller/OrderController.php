<?php

namespace App\Controller;

use Src\Mysql;
use Fuxuqiang\Framework\Request;
use App\Model\Region;

class OrderController
{
    /**
     * 下单
     */
    public function add($address_id, Request $request)
    {
        // 参数验证
        $request->validate(['skus.*.id' => 'int|required', 'skus.*.num' => 'int|required']);

        $userId = $request->userId();

        if (
            !$address = Mysql::table('address')->cols('code', 'address')->where(['user_id' => $userId, 'id' => $address_id])->get()
        ) { // 地址验证
            return error('不存在的地址');
        }
        // 格式化skus参数
        $skuIds = array_column($request->skus, 'id');
        $skus = array_column($request->skus, 'num', 'id');

        Mysql::begin();
        try {
            // sku详情
            $skuModels = \App\Model\Sku::where('stock', '>', 0)->lock()->find($skuIds, ['id', 'name', 'price', 'stock']);
            if (!$skuModels || count($skuIds) > count($skuModels)) {
                throw new \Exception('商品不存在或已售罄');
            }
            // 写入订单表
            $id = Mysql::table('order')->insert([
                'user_id' => $userId,
                'region_code' => $address->code,
                'address' => $address->address,
                'expire_at' => timestamp($_SERVER['REQUEST_TIME'] + 600)
            ]);

            foreach ($skuModels as $skuModel) {
                $sku = $skuModel->get();
                $num = $skus[$sku['id']];
                if ($sku['stock'] < $num) {
                    throw new \Exception($sku['name'] . '库存不足');
                }
                $orderSkus[] = [$id, $sku['id'], $sku['price'], $num, $sku['name']];
                // 库存变动
                $skuModel->io(['order_id' => $id, 'num' => -$num]);
            }
            // 写入订单明细表
            Mysql::table('order_sku')->cols('order_id', 'sku_id', 'price', 'num', 'name')
                ->insert($orderSkus);

            Mysql::commit();

            return msg('下单成功');

        } catch (\Exception $e) {
            Mysql::rollback();
            return error($e->getMessage());
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
                ) . $order->address,
            'skus' => Mysql::table('order_sku')->where('order_id', $id)->all('sku_id', 'name', 'price', 'num')
        ];
    }
}
