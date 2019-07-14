<?php
namespace controller;

use src\Mysql;

class AdminController 
{
    public function create(int $phone, int $role_id)
    {
        $admin = auth();
        $role = Mysql::query('SELECT `is_super` FROM `role` WHERE `id`=?', 'i', [$admin->role_id])->fetch_row();
        if ($role[0]) {
            return \model\Auth::register('admin', ['phone' => $phone, 'role_id' => $role_id]);
        } else {
            response(401);
        }
    }
}
