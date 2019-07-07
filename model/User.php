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
}
