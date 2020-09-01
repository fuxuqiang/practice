<?php

namespace tests;

use src\Mysql;

class SkuControllerTest extends TestCase
{
    public function testAdd()
    {
        $this->admin(1)->post('admin/sku', ['name' => '商品1', 'price' => 100])->assertOk();
        return Mysql::getMysqli()->insert_id;
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

    /**
     * @depends testAdd
     */
    public function testDel($id)
    {
        $this->admin(1)->delete('admin/sku', ['id' => $id]);
        $this->assertTrue(Mysql::table('sku')->whereNotNull('deleted_at')->exists('id', $id));
    }

    public function testGetIoRecords()
    {
        echo $this->admin(1)->get('admin/sku/io_records')->assertOk();
    }
}
