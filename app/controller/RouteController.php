<?php
namespace app\controller;

class RouteController
{
    public function index()
    {
        return [
            'data' => mysql()->query(
                    'SELECT `id`,`method`,CONCAT("admin/",`uri`) AS `uri`,`resource`,`action`
                    FROM `route`'
                )->fetch_all(MYSQLI_ASSOC)
        ];
    }

    public function update(int $id)
    {
        $input = input(['method', 'uri', 'resource', 'action']);
        mysql('route')->where('id', $id)->update($input);
        return ['msg' => '修改成功'];
    }

    public function delete(int $id)
    {
        if (mysql('route')->exists('id', $id)) {
            return ['error' => '不存在的路由'];
        }
        if (mysql('role_route')->exists('route_id', $id)) {
            return ['error' => '存在已绑定的角色关系'];
        }
        mysql()->query('DELETE FROM `route` WHERE `id`=?', [$id]);
        return ['msg' => '删除成功'];
    }
}
