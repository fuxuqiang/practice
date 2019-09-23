<?php
namespace app\controller;

use src\Request;

class SkuController
{
    public function add($name, Request $request)
    {
        $request->validate(['price' => 'int|min:0', 'num' => 'int|min:0']);
        mysql('sku')->insert(['name' => $name, 'price' => $request->price, 'num' => $request->num]);
        return ['msg' => '添加成功'];
    }

    public function list(Request $request)
    {
        return mysql('sku')->paginate(...$request->pageParams());
    }

    public function update($id, Request $request)
    {
        $request->validate(['price' => 'int|min:0', 'num' => 'int|min:0']);
        $input = $request->get('name', 'price', 'num');
        mysql('sku')->where('id', $id)->update($input);
        return ['msg' => '更新成功'];
    }

    public function del($id)
    {
        mysql('sku')->del($id);
        return ['msg' => '删除成功'];
    }
}
