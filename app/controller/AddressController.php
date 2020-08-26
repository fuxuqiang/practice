<?php

namespace app\controller;

use src\Mysql;
use vendor\Request;
use app\model\Region;

class AddressController
{
    /**
     * 添加
     */
    public function add(Request $request, $address = '')
    {
        $request->validate(['code' => 'exists:region,code']);
        Mysql::table('address')->insert([
            'user_id' => $request->userId(),
            'code' => $request->code,
            'address' => $address
        ]);
        return msg('添加成功');
    }

    /**
     * 详情
     */
    public function show($id, Request $request)
    {
        if ($address = Mysql::table('address')->where('id', $id)->where('user_id', $request->userId())->get()) {
            return ['id' => $address->id, 'codes' => Region::getAllCode($address->code), 'address' => $address->address];
        }
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $addresses = Mysql::table('address')->where('user_id', $request->userId())->all();
        $codes = [];
        foreach ($addresses as &$address) {
            $address['codes'] = Region::getAllCode($address['code']);
            $codes = array_merge($codes, $address['codes']);
        }
        $regions = Mysql::table('region')->whereIn('code', $codes)->col('name', 'code');
        return [
            'data' => array_map(function ($val) use ($regions) {
                return [
                    'id' => $val['id'],
                    'code' => $val['code'],
                    'address' => implode('', (new \vendor\Arr($regions))->get(...$val['codes'])) . $val['address']
                ];
            }, $addresses)
        ];
    }

    /**
     * 更新
     */
    public function update($id, Request $request)
    {
        $input = $request->get('code', 'address');
        if (isset($input['code']) && !Mysql::table('region')->exists('code', $input['code'])) {
            return error('行政区不存在');
        }
        Mysql::table('address')->where(['id' => $id, 'user_id' => $request->userId()])->update($input);
        return msg('更新成功');
    }

    /**
     * 删除
     */
    public function del($id)
    {
        Mysql::table('address')->del($id);
        return msg('删除成功');
    }
}
