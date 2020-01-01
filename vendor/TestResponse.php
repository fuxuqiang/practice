<?php

namespace vendor;

use PHPUnit\Framework\Assert;

class TestResponse extends ObjectAccess
{
    public function __call($name, $args)
    {
        return Assert::$name($args[0], $this->data);
    }

    public function assertStatus($status)
    {
        return Assert::assertEquals($status, $this->isException() ? $this->data->getCode() : 200);
    }

    public function assertOk()
    {
        return $this->assertStatus(200);
    }

    public function isException()
    {
        return $this->data instanceof \Exception;
    }
}
