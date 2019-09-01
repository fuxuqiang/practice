<?php
namespace app\controller;

class AdminController 
{
    public function list()
    {
        $input = input();
        $cond = [];
        isset($input['name']) && $cond[] = ['name', 'LIKE', '%'.$input['name'].'%'];
        isset($input['role_id']) && $cond['role_id'] = $input['role_id'];
        return mysql('admin')->cols('id', 'phone', 'name', 'role_id', 'joined_at')->where($cond)
                ->with(['role' => ['id', 'name']])->paginate(...pageParams());
    }

    public function add($phone, $role_id, $name = '')
    {
        validate(['phone' => 'phone', 'role_id' => 'exists:role,id']);
        return \app\model\Auth::registerPhone('admin', $phone, function () use ($phone, $role_id, $name) {
                mysql('admin')->insert([
                    'phone' => $phone,
                    'role_id' => $role_id,
                    'name' => $name,
                    'joined_at' => date('Y-m-d')
                ]);
            }, ['msg' => '添加成功']);
    }

    public function update($name)
    {
        auth()->update(['name' => $name]);
        return ['msg' => '修改成功'];
    }

    public function del($id)
    {
        mysql('admin')->del($id);
        return ['msg' => '删除成功'];
    }

    public function setRole($id, $role_id)
    {
        validate(['role_id' => 'exists:role,id']);
        mysql('admin')->where('id', $id)->update(['role_id' => $role_id]);
        return ['msg' => '设置成功'];
    }
}
