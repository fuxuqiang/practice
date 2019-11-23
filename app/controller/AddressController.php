<?php
namespace app\controller;

use vendor\Request;

class AddressController
{
    public function add(Request $request, $address)
    {
        $request->validate(['code' => 'exists:region,code']);
        mysql('address')->insert([
            'user_id' => $request->user()->id,
            'code' => $request->code,
            'address' => $address
        ]);
        return ['msg' => '添加成功'];
    }

    public function list(Request $request)
    {
        $addresses = mysql('address')->where('user_id', $request->user()->id)->all();
        $codes = [];
        foreach ($addresses as &$address) {
            $address['codes'] = \app\model\Region::getAllCode($address['code']);
            $codes = array_merge($codes, $address['codes']);
        }
        $regions = mysql('region')->whereIn('code', $codes)->col('name', 'code');
        return [
            'data' => array_map(function ($val) use ($regions) {
                return [
                    'id' => $val['id'],
                    'code' => $val['code'],
                    'address' => implode('', (new \vendor\Arr($regions))->get(...$val['codes'])).$val['address']
                ];
            }, $addresses)
        ];
    }

    public function update($id, Request $request)
    {
        $input = $request->get('code', 'address');
        if (isset($input['code']) && ! mysql('region')->exists('code', $input['code'])) {
            return ['error' => '行政区不存在'];
        }
        mysql('address')->where(['id' => $id, 'user_id' => $request->user()->id])->update($input);
        return ['msg' => '更新成功'];
    }

    public function del($id)
    {
        mysql('address')->del($id);
        return ['msg' => '删除成功'];
    }
}
