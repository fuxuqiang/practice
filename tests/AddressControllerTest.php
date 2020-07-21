<?php

namespace tests;

use src\Mysql;

class AddressControllerTest extends TestCase
{
    public function testList()
    {
        echo $this->user(1)->get('user/addresses')->assertOk();
    }

    public function testShow()
    {
        echo $this->user(1)->get('user/address', ['id' => 4])->assertOk();
    }

    public function testAdd()
    {
        $data = ['code' => 11, 'address' => '秀山南路'];
        $this->user(1)->post('user/address', $data);
        $id = Mysql::getMysqli()->insert_id;
        $this->assertDatabaseHas('address', $data);
        return $id;
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($id)
    {
        $data = ['address' => '秀山巷', 'code' => 141023105224, 'id' => $id];
        $this->user(1)->put('user/address', $data);
        $this->assertDatabaseHas('address', $data);
    }

    /**
     * @depends testAdd
     */
    public function testDel($id)
    {
        $data = ['id' => $id];
        $this->user(1)->delete('user/address', $data);
        $this->assertTrue(Mysql::table('address')->where($data)->count() == 0);
    }
}