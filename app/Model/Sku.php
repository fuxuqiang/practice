<?php

namespace App\Model;

class Sku extends \Fuxuqiang\Framework\Model
{
    public function io($data)
    {
        $this->inc('stock', $data['num']);
        \Src\Mysql::table('sku_record')->insert($data + ['sku_id' => $this->id]);
    }
}
