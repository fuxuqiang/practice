<?php
namespace controller;

use src\Mysql;

class AdminController 
{
    public function index($perPage)
    {
        if (auth()->isSuper()) {
            return ['data' => mysql('admin')->paginate($perPage)];
        } else {
            response(401);
        }
    }

    public function create(int $phone, int $role_id = 0, $name = '')
    {
        if (auth()->isSuper()) {
            return \model\Auth::registerPhone('admin', $phone, function () use ($phone, $role_id, $name) {
                mysql('admin')->insert(['phone' => $phone, 'role_id' => $role_id, 'name' => $name]);
            }, ['msg' => '添加成功']);
        } else {
            response(401);
        }
    }

    public function update($name)
    {
        auth()->update(['name' => $name]);
        return ['msg' => '修改成功'];
    }
}
