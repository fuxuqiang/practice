<?php

namespace tests;

use src\Container;

class AuthControllerTest extends \src\TestCase
{
    private $beforePhone = 12345678901, $afterPhone = 12123456789, $password = 'a12345';

    public function testSendCode()
    {
        $code = $this->getCode($this->beforePhone);
        $this->assertTrue($code <= 9999);
        $this->assertTrue($code > 999);
        return $code;
    }

    /**
     * @depends testSendCode
     */
    public function testUserLogin($code)
    {
        $response = $this->post('login', [
            'phone' => $this->beforePhone,
            'code' => $code
        ]);
        $this->assertArrayHasKey('data', $response);
        $this->assertTrue(mysql('user')->where('phone', $this->beforePhone)->val('id') > 0);
        return $response['data'];
    }

    /**
     * @depends testUserLogin
     */
    public function testSetPassword($token)
    {
        $this->assertArrayHasKey(
            'msg',
            $this->put('user/password', ['password' => $this->password], $token)
        );
        $this->assertArrayHasKey('data', $this->post('login', [
            'phone' => $this->beforePhone,
            'password' => $this->password
        ]));
    }

    /**
     * @depends testUserLogin
     */
    public function testChangePhone($token)
    {
        $this->put('user/phone', [
            'phone' => $this->afterPhone,
            'code' => $this->getCode($this->afterPhone)
        ], $token);
        $this->assertArrayHasKey('data', $this->post('login', [
            'phone' => $this->afterPhone,
            'password' => $this->password
        ]));
        mysql('user')->where('phone', $this->afterPhone)->del();
    }

    public function testAdminLogin()
    {
        $this->assertArrayHasKey('data', $this->post('admin/login', [
            'phone' => 18005661486,
            'code' => $this->getCode(18005661486)
        ]));
    }

    private function getCode($phone)
    {
        $this->post('sendCode', ['phone' => $phone]);
        return Container::get('Redis')->get($phone);
    }
}
