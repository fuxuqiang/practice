<?php

namespace tests;

class AdminControllerTest extends \src\TestCase
{
    public function testList()
    {
        $this->assertIsArray($this->get('admin/admins', [], \src\Container::get('src\JWT')->encode(1)));
    }
}