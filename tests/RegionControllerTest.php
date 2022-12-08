<?php

namespace Tests;

class RegionControllerTest extends \Src\TestCase
{
    public function testList()
    {
        echo $this->get('regions', ['code' => 3417])->assertOk();
    }

    public function testParseAddress()
    {
        echo $this->get('parseAddress', ['address' => '安徽省池州市贵池区池阳街道秀山巷'])->assertOk();
        echo $this->get('parseAddress', ['address' => '沛县人民路'])->assertOk();
    }
}