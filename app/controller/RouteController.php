<?php
namespace app\controller;

class RouteController
{
    public function index(int $page = 1, int $per_page = 5, $resource = false)
    {
        $query = mysql('route');
        $resource && $query->where([['resource', 'like', '%'.$resource.'%']]);
        return $query->paginate($page, $per_page);
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
