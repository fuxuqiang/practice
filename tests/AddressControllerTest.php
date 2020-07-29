<?php

namespace tests;

use src\Mysql;

class AddressControllerTest extends TestCase
{
    public function testAdd()
    {
        $data = ['code' => 11, 'address' => '秀山南路'];
        $this->user(1)->post('user/address', $data)->assertOk();
        return Mysql::getMysqli()->insert_id;
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($id)
    {
        $this->user(1)->put('user/address', ['address' => '秀山巷', 'code' => 141023105224, 'id' => $id])->assertOk();
    }

    public function testList()
    {
        echo $this->user(1)->get('user/addresses')->assertOk();
    }

    /**
     * @depends testAdd
     */
    public function testShow($id)
    {
        echo $this->user(1)->get('user/address', ['id' => $id])->assertArraySubset(['id' => $id, 'address' => '秀山巷']);
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
