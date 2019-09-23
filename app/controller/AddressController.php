<?php
namespace app\controller;

class AddressController
{
    public function add($code, $address)
    {
        validate(['code' => 'exists:region,code']);
        mysql('address')->insert(['user_id' => auth()->id, 'code' => $code, 'address' => $address]);
        return ['msg' => '添加成功'];
    }

    public function list()
    {
        $addresses = mysql('address')->where('user_id', auth()->id)->all();
        $codes = [];
        foreach ($addresses as &$address) {
            $address['codes'] = \app\model\Region::getAllCode($address['code']);
            $codes = array_merge($codes, $address['codes']);
        }
        $regions = mysql('region')->whereIn('code', $codes)->col('name', 'code');
        $addresses = array_map(function ($val) use ($regions) {
            return [
                'id' => $val['id'],
                'code' => $val['code'],
                'address' => implode('', arrayOnly($regions, $val['codes'])).$val['address']
            ];
        }, $addresses);
        return ['data' => $addresses];
    }

    public function update($id)
    {
        $input = input(['code', 'address']);
        if (isset($input['code']) && ! mysql('region')->exists('code', $code)) {
            return ['error' => '行政区不存在'];
        }
        mysql('address')->where(['id' => $id, 'user_id' => auth()->id])->update($input);
        return ['msg' => '更新成功'];
    }

    public function del($id)
    {
        mysql('address')->del($id);
        return ['msg' => '删除成功'];
    }
}
