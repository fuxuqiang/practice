<?php

namespace tests;

class SkuControllerTest extends TestCase
{
    public function testAdd()
    {
        $this->admin(1)->post('admin/sku', ['name' => '商品1', 'price' => 100, 'num' => 2])->assertOk();
        return \src\Mysql::getMysqli()->insert_id;
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($id)
    {
        $data = ['price' => 200, 'id' => $id];
        $this->admin(1)->put('admin/sku', $data);
        $this->assertDatabaseHas('sku', $data);
    }
    
    public function testList()
    {
        echo $this->admin(1)->get('admin/skus')->assertOk();
    }
}
