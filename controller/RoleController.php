<?php
namespace controller;

class RoleController
{
    public function index(int $page, int $perPage)
    {
        return [
            'data' => msyql('role')->paginate($page, $perPage)
        ];
    }

    public function create($name, int $pid)
    {
        mysql('role')->insert(['name' => $name, 'pid' => $pid]);
        return ['msg' => '添加成功'];
    }
}
