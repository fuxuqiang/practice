<?php

namespace Tests;

class RegionControllerTest extends \Src\TestCase
{
    public function testList()
    {
        echo $this->get('regions', ['pCode' => 3417])->assertOk();
    }

    public function testGetRegionCode()
    {
        echo $this->get('getRegionCode', ['address' => '安徽省池州市贵池区殷汇镇'])->assertOk();
        echo $this->get('getRegionCode', ['address' => '沛县'])->assertOk();
    }
}