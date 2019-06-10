<?php
namespace model;

use src\Mysql;

class User
{
    public $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function update(array $data)
    {
        $sql = 'UPDATE `user` SET ';
        $types = '';
        foreach ($data as $key => $value) {
            $types .= 's';
            $sql .= '`'.$key.'`=?,';
        }
        $sql = rtrim($sql, ',').' WHERE `id`='.$this->id;
        Mysql::query($sql, $types, $data);
    }
}
