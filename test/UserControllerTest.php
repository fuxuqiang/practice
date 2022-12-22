<?php

namespace Test;

use Src\{Redis, TestCase};

class UserControllerTest extends TestCase
{
    public function testSendCode()
    {
        $mobile = 12345678901;
        $this->post('sendCode', ['mobile' => $mobile]);
        $this->assertIsNumeric(Redis::get($mobile));
    }
}