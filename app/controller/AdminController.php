<?php

namespace app\controller;

use src\Mysql;
use vendor\Request;

class AdminController
{
    /**
     * 列表
     */
    public function list(Request $request)
    {
        $input = $request->get();
        $cond = [];
        isset($input['name']) && $cond[] = ['name', 'LIKE', '%' . $input['name'] . '%'];
        isset($input['role_id']) && $cond['role_id'] = $input['role_id'];
        return Mysql::table('admin')->cols('id', 'phone', 'name', 'role_id', 'joined_at')->where($cond)
            ->with(['role' => ['id', 'name']])->paginate(...$request->pageParams());
    }

    /**
     * 新增
     */
    public function add(Request $request, $name = '')
    {
        $request->validate(['phone' => 'unique:admin,phone', 'role_id' => 'exists:role,id']);
        Mysql::table('admin')->insert(
            $request->get('phone', 'role_id') + ['joined_at' => date('Y-m-d'), 'name' => $name]
        );
        return ['msg' => '添加成功'];
    }

    /**
     * 修改管理员名
     */
    public function update($name, Request $request)
    {
        $request->user()->update(['name' => $name]);
        return ['msg' => '修改成功'];
    }

    /**
     * 删除
     */
    public function del($id)
    {
        Mysql::table('admin')->del($id);
        return ['msg' => '删除成功'];
    }

    /**
     * 设置角色
     */
    public function setRole($id, Request $request)
    {
        $request->validate(['role_id' => 'exists:role,id']);
        Mysql::table('admin')->where('id', $id)->update(['role_id' => $request->role_id]);
        return ['msg' => '设置成功'];
    }
}
