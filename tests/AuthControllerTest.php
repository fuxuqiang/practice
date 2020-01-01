<?php

namespace tests;

class AuthControllerTest extends TestCase
{
    private $beforePhone = 12345678901, $password = 'a12345';

    public function testUserLogin()
    {
        $response = $this->post(
            'login',
            ['phone' => $this->beforePhone, 'code' => $this->getCode($this->beforePhone)]
        );
        $response->assertArrayHasKey('data');
        $this->assertTrue(
            $this->post('login', ['phone' => $this->beforePhone, 'code' => 1])->isException()
        );
        return $response->data;
    }

    /**
     * @depends testUserLogin
     */
    public function testSetPassword($token)
    {
        $this->put('user/password', ['password' => $this->password], $token);
        $this->put('user/password', ['password' => $this->password], $token)->assertStatus(401);
        $response = $this->post(
            'login',
            ['phone' => $this->beforePhone, 'password' => $this->password]
        );
        $response->assertArrayHasKey('data');
        return $response->data;
    }

    /**
     * @depends testSetPassword
     */
    public function testChangePhone($token)
    {
        $phone = 12123456789;
        $this->put('user/phone', ['phone' => $phone, 'code' => $this->getCode($phone)], $token);
        $this->post('login', ['phone' => $phone,'password' => $this->password])
            ->assertArrayHasKey('data');
    }
}
