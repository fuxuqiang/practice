<?php
namespace controller;

use src\Mysql;

class AdminController 
{
    public function index($perPage)
    {
        return [
            'data' => mysql('admin')
                ->select('id', 'phone', 'name', 'role_id', 'created_at')->paginate($perPage)
        ];
    }

    public function create(int $phone, int $role_id = 0, $name = '')
    {
        return \model\Auth::registerPhone('admin', $phone, function () use ($phone, $role_id, $name) {
                mysql('admin')->insert(['phone' => $phone, 'role_id' => $role_id, 'name' => $name]);
            }, ['msg' => '添加成功']);
    }

    public function update($name)
    {
        auth()->update(['name' => $name]);
        return ['msg' => '修改成功'];
    }
}
