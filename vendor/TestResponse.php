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
        Assert::$name($args[0], $this->data);
        return $this;
    }

    public function assertStatus($status)
    {
        Assert::assertEquals($status, $this->status);
        return $this;
    }

    public function assertOk()
    {
        return $this->assertStatus(200);
    }

    public function __toString()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
