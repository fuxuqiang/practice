<?php

namespace app\model;

class Sku extends \vendor\Model
{
    public function io($data)
    {
        $this->inc('stock', $data['num']);
        \src\Mysql::table('sku_record')->insert($data + ['sku_id' => $this->id]);
    }
}
