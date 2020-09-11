<?php
namespace App\Controller;

use Src\Mysql;
use Fuxuqiang\Framework\Request;

class RoleController
{
    /**
     * 列表
     */
    public function list()
    {
        return ['data' => Mysql::table('role')->all()];
    }

    /**
     * 添加
     */
    public function add($name, Request $request)
    {
        $request->validate(['pid' => 'exists:role,id']);
        Mysql::table('role')->insert(['name' => $name, 'pid' => $request->pid]);
        return msg('添加成功');
    }

    /**
     * 更新
     */
    public function update($id, Request $request)
    {
        $roles = Mysql::table('role')->all('id', 'pid');
        $ids = array_column($roles, 'id');
        if (!in_array($id, $ids)) {
            return error('操作的角色不存在');
        }
        $input = $request->get('name', 'pid');
        if (isset($input['pid'])) {
            if (!in_array($input['pid'], $ids)) {
                return error('上级角色不存在');
            }
            if (inSubs($input['pid'], $roles, $id) || $input['pid'] == $id) {
                return error('上级角色不能为自身或下级角色');
            }
        }
        Mysql::table('role')->where('id', $id)->update($input);
        return msg('更新成功');
    }

    /**
     * 删除
     */
    public function del($id)
    {
        if (Mysql::table('role')->exists('pid', $id)) {
            return error('存在子级角色');
        }
        if (Mysql::table('admin')->exists('role_id', $id)) {
            return error('存在该角色的用户');
        }
        Mysql::table('role')->del($id);
        return msg('删除成功');
    }

    /**
     * 路由列表
     */
    public function listRoutes()
    {
        return [
            'data' => Mysql::select(
                    'SELECT `id`,`method`,CONCAT("admin/",`uri`) AS `uri`,`resource`,`action`
                    FROM `route`'
                )
        ];
    }

    /**
     * 保存路由
     */
    public function saveRoutes(Request $request, $id)
    {
        $request->validate(['id' => 'exists:role,id', 'route_ids' => 'array']);
        $routeIds = Mysql::table('route')->whereIn('id', $request->route_ids)->col('id');
        Mysql::table('role_route')->cols('role_id', 'route_id')->replace(
            array_map(function ($val) use ($id) {
                return [$id, $val];
            }, $routeIds)
        );
        return msg('保存成功');
    }
}
