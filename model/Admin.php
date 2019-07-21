<?php
namespace model;

class Admin extends \src\Model
{
    public function isSuper()
    {
        return !$this->query('SELECT `pid` FROM `role` WHERE `id`=?', 'i', [$this->role_id])->fetch_row()[0];
    }
}
