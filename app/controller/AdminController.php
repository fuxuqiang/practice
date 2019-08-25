<?php
namespace app\controller;

class AdminController 
{
    public function list(int $page = 1, int $per_page = 5)
    {
        $input = input();
        $cond = [];
        isset($input['name']) && $cond[] = ['name', 'LIKE', '%'.$input['name'].'%'];
        isset($input['role_id']) && $cond['role_id'] = $input['role_id'];
        return mysql('admin')->cols('id', 'phone', 'name', 'role_id', 'created_at')->where($cond)
                ->with(['role' => ['id', 'name']])->whereNull('deleted_at')->paginate($page, $per_page);
    }

    public function add(int $phone, int $role_id = 0, $name = '')
    {
        validateRoleId($role_id);
        return \model\Auth::registerPhone('admin', $phone, function () use ($phone, $role_id, $name) {
                mysql('admin')->insert(['phone' => $phone, 'role_id' => $role_id, 'name' => $name]);
            }, ['msg' => '添加成功']);
    }

    public function update($name)
    {
        auth()->update(['name' => $name]);
        return ['msg' => '修改成功'];
    }

    public function del(int $id)
    {
        mysql('admin')->where('id', $id)->update(['deleted_at' => timestamp()]);
        return ['msg' => '删除成功'];
    }

    public function setRole(int $id, int $role_id)
    {
        validateRoleId($role_id);
        mysql('admin')->where('id', $id)->update(['role_id' => $role_id]);
        return ['msg' => '设置成功'];
    }
}
