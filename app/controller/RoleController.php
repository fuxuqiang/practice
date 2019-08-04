<?php
namespace app\controller;

class RoleController
{
    public function index()
    {
        return ['data' => mysql('role')->get()];
    }

    public function create($name, int $pid)
    {
        validateRoleId($pid);
        mysql('role')->insert(['name' => $name, 'pid' => $pid]);
        return ['msg' => '添加成功'];
    }

    public function update(int $id)
    {
        $roles = mysql('role')->select('id', 'pid')->get();
        $ids = array_column($roles, 'id');
        if (!in_array($id, $ids)) {
            return ['error' => '操作的角色不存在'];
        }
        $input = input(['name', 'pid']);
        if (isset($input['pid'])) {
            if (!in_array($input['pid'], $ids)) {
                return ['error' => '上级角色不存在'];
            }
            if (inSubs($input['pid'], $roles, $id) || $input['pid'] == $id) {
                return ['error' => '上级角色不能为自身或下级角色'];
            }
        }
        mysql('role')->where('id', $id)->update($input);
        return ['msg' => '更新成功'];
    }

    public function delete(int $id)
    {
        if (mysql('role')->where('pid', $id)->exists()) {
            return ['error' => '存在子级角色'];
        }
        if (mysq('admin')->where('role_id', $id)->exists()) {
            return ['error' => '存在该角色的用户'];
        }
        if (mysql('role_route')->select('route_id')->where('role_id', $id)->exists()) {
            return ['error' => '该角色的权限未清空'];
        }
        mysql()->query('DELETE FROM `role` WHERE `id`=?', 'i', [$id]);
        return ['msg' => '删除成功'];
    }
}
