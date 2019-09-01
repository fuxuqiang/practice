<?php
namespace app\controller;

class SkuController
{
    public function add($name, $price, $num)
    {
        validate(['price' => 'int|min:0', 'num' => 'int|min:0']);
        mysql('sku')->insert(['name' => $name, 'price' => $price, 'num' => $num]);
        return ['msg' => '添加成功'];
    }

    public function list()
    {
        return mysql('sku')->paginate(...pageParams());
    }

    public function update($id)
    {
        validate(['price' => 'int|min:0', 'num' => 'int|min:0']);
        $input = input(['name', 'price', 'num']);
        mysql('sku')->where('id', $id)->update($input);
        return ['msg' => '更新成功'];
    }

    public function del($id)
    {
        mysql('sku')->del($id);
        return ['msg' => '删除成功'];
    }
}
