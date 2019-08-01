<?php
namespace controller;

class RoleController
{
    public function index(int $page, int $per_page)
    {
        return [
            'data' => msyql('role')->paginate($page, $per_page)
        ];
    }

    public function create($name, int $pid)
    {
        validateRoleId($pid);
        mysql('role')->insert(['name' => $name, 'pid' => $pid]);
        return ['msg' => '添加成功'];
    }

    public function update(int $id)
    {
        validateRoleId($id);
        $input = input(['name', 'pid']);
        empty($input['pid']) || validateRoleId($input['pid']);
        mysql('role')->where('id', $id)->update($input);
        return ['msg' => '更新成功'];
    }
}
