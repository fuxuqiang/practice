<?php

namespace Test;

class FundControllerTest extends \Src\TestCase
{
    public function testGetWorth()
    {
        $this->get('getData', ['id' => 1])->assertOk()->print();
    }

    public function testBuy()
    {
        $this->post('buy', ['id' => 1, 'amount' => 40000, 'date' => '2019-12-27'])->assertOk()->print();
    }
}