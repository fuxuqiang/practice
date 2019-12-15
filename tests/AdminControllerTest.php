<?php

namespace tests;

use src\Mysql;

class AdminControllerTest extends TestCase
{
    private $phone = 12345678901;

    public function testAdminLogin()
    {
        $adminPhone = 18005661486;
        $this->assertArrayHasKey(
            'data',
            $response = $this->post('admin/login', ['phone' => $adminPhone, 'code' => $this->getCode($adminPhone)])
        );
        $this->assertTrue(
            '请输入验证码' == $this->post('admin/login', ['phone' => $adminPhone, 'password' => 1])['error']
        );
        return $response['data'];
    }

    /**
     * @depends testAdminLogin
     */
    public function testList($token)
    {
        $this->assertIsArray($this->get('admin/admins', [], $token));
    }

    /**
     * @depends testAdminLogin
     */
    public function testRoleAdd($token)
    {
        $this->assertArrayHasKey('msg', $this->post('admin/role', ['name' => 'test', 'pid' => 1], $token));
        return Mysql::table('role')->where('name', 'test')->val('id');
    }

    /**
     * @depends testAdminLogin
     * @depends testRoleAdd
     */
    public function testAdd($token, $id)
    {
        $this->assertArrayHasKey(
            'msg',
            $this->post('admin/admin', ['phone' => $this->phone, 'role_id' => $id], $token)
        );
    }

    /**
     * @depends testAdd
     */
    public function testSetPassword($token)
    {
        $password = 'a12345';
        $this->put(
            'admin/password',
            ['password' => $password],
            $this->post('admin/login', ['phone' => $this->phone, 'code' => $this->getCode($this->phone)])['data']
        );
        $this->assertArrayHasKey(
            'data',
            $response = $this->post('admin/login', ['phone' => $this->phone, 'password' => $password])
        );
        return $response['data'];
    }

    /**
     * @depends testSetPassword
     */
    public function testUpdate($token)
    {
        $this->assertArrayHasKey('msg', $this->put('admin/adminName', ['name' => 'a'], $token));
    }

    /**
     * @depends testSetPassword
     * @depends testRoleAdd
     */
    public function testSetRole($token, $id)
    {
        $this->assertArrayHasKey(
            'msg',
            $this->put(
                'admin/adminRole',
                ['role_id' => $id, 'id' => Mysql::table('admin')->where('phone', $this->phone)->val('id')],
                $token
            )
        );
    }
    
    /**
     * @depends testSetPassword
     */
    public function testDel($token)
    {
        $this->assertArrayHasKey(
            'msg',
            $this->delete('admin/admin', ['id' => Mysql::table('admin')->where('phone', $this->phone)->val('id')], $token)
        );
    }

    /**
     * @depends testAdminLogin
     * @depends testRoleAdd
     */
    public function testRoleDel($token, $id)
    {
        $this->assertArrayHasKey('msg', $this->delete('admin/role', ['id' => $id], $token));
    }
}
