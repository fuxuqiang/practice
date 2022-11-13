<?php

namespace Tests;

class RegionControllerTest extends \Src\TestCase
{
    public function testList()
    {
        echo $this->get('regions', ['code' => 3417])->assertOk();
    }

    public function testParseRegion()
    {
        echo $this->get('parseRegion', ['address' => '安徽省池州市贵池区秀山巷'])->assertOk();
        echo $this->get('parseRegion', ['address' => '沛县'])->assertOk();
    }
}