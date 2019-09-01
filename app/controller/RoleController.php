<?php
namespace app\controller;

class RoleController
{
    /**
     * 列表
     */
    public function list()
    {
        return ['data' => mysql('role')->all()];
    }

    /**
     * 添加
     */
    public function add($name, $pid)
    {
        validate(['pid' => 'exists:role,id']);
        mysql('role')->insert(['name' => $name, 'pid' => $pid]);
        return ['msg' => '添加成功'];
    }

    /**
     * 更新
     */
    public function update($id)
    {
        $roles = mysql('role')->all(['id', 'pid']);
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

    /**
     * 删除
     */
    public function del($id)
    {
        if (mysql('role')->exists('pid', $id)) {
            return ['error' => '存在子级角色'];
        }
        if (mysq('admin')->exists('role_id', $id)) {
            return ['error' => '存在该角色的用户'];
        }
        if (mysql('role_route')->exists('role_id', $id)) {
            return ['error' => '该角色的权限未清空'];
        }
        mysql('role')->del($id);
        return ['msg' => '删除成功'];
    }

    /**
     * 路由列表
     */
    public function listRoutes()
    {
        return [
            'data' => mysql()->query(
                    'SELECT `id`,`method`,CONCAT("admin/",`uri`) AS `uri`,`resource`,`action`
                    FROM `route`'
                )->fetch_all(MYSQLI_ASSOC)
        ];
    }

    /**
     * 保存路由
     */
    public function saveRoutes($id, $route_ids)
    {
        validate(['id' => 'exists:role,id', 'route_ids' => 'array']);
        $routeIds = mysql('route')->whereIn('id', $route_ids)->col('id');
        if (array_diff($route_ids, $routeIds)) {
            return ['error' => '存在未定义的路由'];
        }
        mysql('role_route')->cols('role_id', 'route_id')->replace(
            array_map(function ($val) use ($id) {
                return [$id, $val];
            }, $routeIds)
        );
        return ['msg' => '保存成功'];
    }
}
