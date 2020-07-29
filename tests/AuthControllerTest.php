<?php

namespace tests;

class AuthControllerTest extends TestCase
{
    private $beforeMobile = 12345678901, $password = 'a12345';

    public function testUserLogin()
    {
        $response = $this->post(
            'login',
            ['mobile' => $this->beforeMobile, 'code' => $this->getCode($this->beforeMobile)]
        );
        $response->assertArrayHasKey('data');
        $this->post('login', ['mobile' => $this->beforeMobile, 'code' => 1])->assertArrayHasKey('error');
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
            ['mobile' => $this->beforeMobile, 'password' => $this->password]
        );
        $response->assertArrayHasKey('data');
        return $response->data;
    }

    /**
     * @depends testSetPassword
     */
    public function testChangeMobile($token)
    {
        $mobile = 12123456789;
        $this->put('user/mobile', ['mobile' => $mobile, 'code' => $this->getCode($mobile)], $token);
        $this->post('login', ['mobile' => $mobile,'password' => $this->password])
            ->assertArrayHasKey('data');
    }

    /**
     * @depends testSetPassword
     */
    public function testRegisterMerchant($token)
    {
        $data = ['name' => '测试', 'credit_code' => 'CS001'];
        $this->post('registerMerchant', $data, $token);
        $this->assertDatabaseHas('merchant', $data);
    }
}
