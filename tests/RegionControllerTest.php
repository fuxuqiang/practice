<?php

namespace Tests;

class RegionControllerTest extends TestCase
{
    public function testList()
    {
        echo $this->user(1)->get('regions', ['p_code' => 3417])->assertOk();
    }

    public function testGetRegionCode()
    {
        echo $this->user(1)->get('get_region_code', ['address' => '安徽省池州市贵池区秀山南路'])->assertOk();
    }
}