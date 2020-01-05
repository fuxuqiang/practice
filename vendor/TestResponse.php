<?php

namespace vendor;

use PHPUnit\Framework\Assert;

class TestResponse extends ObjectAccess
{
    private $status;

    public function __construct($response, $status)
    {
        $this->data = $response;
        $this->status = $status;
    }

    public function __call($name, $args)
    {
        return Assert::$name($args[0], $this->data);
    }

    public function assertStatus($status)
    {
        return Assert::assertEquals($status, $this->status);
    }

    public function assertOk()
    {
        return $this->assertStatus(200);
    }
}
