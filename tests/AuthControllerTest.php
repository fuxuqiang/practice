<?php

namespace tests;

class AuthControllerTest extends TestCase
{
    private $beforePhone = 12345678901, $password = 'a12345';

    public function testUserLogin()
    {
        $this->assertArrayHasKey(
            'data',
            $response = $this->post(
                'login',
                ['phone' => $this->beforePhone, 'code' => $this->getCode($this->beforePhone)]
            )
        );
        return $response['data'];
    }

    /**
     * @depends testUserLogin
     */
    public function testSetPassword($token)
    {
        $this->put('user/password', ['password' => $this->password], $token);
        $this->assertArrayHasKey(
            'data',
            $response = $this->post(
                'login',
                ['phone' => $this->beforePhone, 'password' => $this->password]
            )
        );
        return $response['data'];
    }

    /**
     * @depends testSetPassword
     */
    public function testChangePhone($token)
    {
        $phone = 12123456789;
        $this->put('user/phone', ['phone' => $phone, 'code' => $this->getCode($phone)], $token);
        $this->assertArrayHasKey(
            'data',
            $this->post('login', ['phone' => $phone,'password' => $this->password])
        );
        mysql('user')->where('phone', $phone)->del();
    }
}
