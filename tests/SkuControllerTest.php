<?php

namespace Tests;

use Src\Mysql;

class SkuControllerTest extends TestCase
{
    use \Src\DatabaseTransaction;

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

    /**
     * @depends testAdd
     */
    public function testDel($id)
    {
        $this->admin(1)->delete('admin/sku', ['id' => $id]);
        $this->assertTrue(Mysql::table('sku')->whereNotNull('deleted_at')->exists('id', $id));
    }

    /**
     * @depends testAdd
     */
    public function testIo($id)
    {
        $this->admin(1)->post('admin/sku/io', ['id' => $id, 'num' => -1])->assertArrayHasKey('error');
        $this->admin(1)->post('admin/sku/io', ['id' => $id, 'num' => 200]);
        $this->assertDatabaseHas('sku', ['id' => $id, 'stock' => 200]);
        $this->assertDatabaseHas('sku_record', ['sku_id' => $id, 'num' => 200]);
    }

    public function testList()
    {
        echo $this->admin(1)->get('admin/skus')->assertOk();
    }

    public function testGetIoRecords()
    {
        echo $this->admin(1)->get('admin/sku/io_records', ['keyword' => '菠萝'])->assertOk();
    }
}
