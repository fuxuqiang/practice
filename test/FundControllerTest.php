<?php

namespace Test;

class FundControllerTest extends \Src\TestCase
{
    public function testGetWorth()
    {
        $this->get('getWorth', ['id' => 1])->assertOk();
    }
}