<?php

namespace Test;

use Fuxuqiang\Framework\ResponseException;
use Src\{Redis, TestCase};

class UserControllerTest extends TestCase
{
    public function testSendCode()
    {
        $mobile = 12345678901;

        $this->post('sendCode', ['mobile' => $mobile]);
        $this->assertIsNumeric(Redis::get($mobile));

        $this->withIP('127.1.1.1')->post('sendCode', ['mobile' => $mobile])->assertOk();

        $this->expectException(ResponseException::class);
        $this->post('sendCode', ['mobile' => $mobile]);
    }
}