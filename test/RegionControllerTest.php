<?php

namespace Test;

class RegionControllerTest extends \Src\TestCase
{
    public function testList()
    {
        $this->get('regions', ['code' => 3417])->assertOk()->print();
    }

    public function testParseAddress()
    {
        $this->get('parseAddress', ['address' => '安徽省池州市贵池区池阳街道秀山巷'])->assertOk()->print();
        $this->get('parseAddress', ['address' => '沛县人民路'])->assertOk()->print();
    }

    public function testSearch()
    {
        $this->get('searchCity', ['name' => 'cz'])->assertOk()->print();
    }
}