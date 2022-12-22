<?php

namespace Test;

use Src\Mysql;

class OrderControllerTest extends TestCase
{
    use \Src\DatabaseTransaction;

    public function testAdd()
    {
        Mysql::table('sku')->del();
        Mysql::table('address')->del();
        Mysql::table('sku')->cols('id', 'name', 'price', 'stock')->insert([
            [1, '商品1', 5000, 100],
            [2, '商品2', 4000, 200]
        ]);
        Mysql::table('address')->insert(['id' => 1, 'user_id' => 1, 'code' => 371328106240, 'address' => '秀山巷']);

        $skus = ['skus' => [['id' => 1, 'num' => 1], ['id' => 2, 'num' => 3]]];

        $this->user(1)->post('order', ['address_id' => 1, 'skus' => [['id' => 3, 'num' => 1]]])->assertArrayHasKey('error');
        $this->user(1)->post('order', ['address_id' => 1, 'skus' => [['id' => 1, 'num' => 101]]])->assertArrayHasKey('error');
        $this->user(1)->post('order', ['address_id' => 1, 'skus' => []])->assertArrayHasKey('error');
        $this->user(1)->post('order', ['address_id' => 7] + $skus)->assertArrayHasKey('error');
        $this->user(1)->post('order', ['address_id' => 1] + $skus)->assertArrayHasKey('msg');
    }
}
