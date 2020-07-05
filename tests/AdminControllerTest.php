<?php

namespace tests;

use src\Mysql;

class AdminControllerTest extends TestCase
{
    private $phone = 12345678901;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Mysql::table('route')->del();
    }

    public function testAdminLogin()
    {
        $adminPhone = 18005661486;
        $response = $this->post(
            'admin/login',
            ['phone' => $adminPhone, 'code' => $this->getCode($adminPhone)]
        );
        $response->assertArrayHasKey('data');
        $this->post('admin/login', ['phone' => $adminPhone, 'password' => 1])
            ->assertArrayHasKey('error');
        return $response->data;
    }

    /**
     * @depends testAdminLogin
     */
    public function testRoleAdd($token)
    {
        $this->post('admin/role', ['name' => 'test', 'pid' => 1], $token)->assertOk();
        return Mysql::table('role')->where('name', 'test')->val('id');
    }

    /**
     * @depends testAdminLogin
     * @depends testRoleAdd
     */
    public function testRoleUpdate($token, $id)
    {
        $this->put('admin/role', ['id' => $id, 'pid' => 1], $token)->assertArrayHasKey('msg');
        $this->put('admin/role', ['id' => $id, 'pid' => $id], $token)->assertArrayHasKey('error');
    }

    /**
     * @depends testAdminLogin
     * @depends testRoleAdd
     */
    public function testAdd($token, $id)
    {
        $this->post('admin/admin', ['phone' => $this->phone, 'role_id' => $id], $token)->assertOk();
        return Mysql::table('admin')->where('phone', $this->phone)->val('id');
    }

    /**
     * @depends testAdminLogin
     */
    public function testList($token)
    {
        $this->get('admin/admins', [], $token)->assertArrayHasKey('total');
    }

    /**
     * @depends testAdd
     */
    public function testSetPassword($id)
    {
        $password = 'a12345';
        $token = $this->getToken($id, 'admin');
        $this->put('admin/password', ['password' => $password], $token);
        $this->put('admin/password', ['password' => $password], $token)->assertStatus(401);
        $response = $this->post('admin/login', ['phone' => $this->phone, 'password' => $password]);
        $response->assertArrayHasKey('data');
        return $response->data;
    }

    /**
     * @depends testSetPassword
     */
    public function testUpdate($token)
    {
        $this->put('admin/adminName', ['name' => 'a'], $token)->assertOk();
    }

    /**
     * @depends testSetPassword
     * @depends testAdd
     * @depends testRoleAdd
     */
    public function testSetRole($token, $id, $roleId)
    {
        $this->put('admin/adminRole', ['role_id' => $roleId, 'id' => $id], $token)->assertOk();
    }

    /**
     * @depends testSetPassword
     */
    public function testListRoles($token)
    {
        $this->get('admin/roles', [], $token)->assertOk();
    }

    /**
     * @depends testSetPassword
     */
    public function testListRoutes($token)
    {
        $this->get('admin/routes', [], $token)->assertOk();
    }

    /**
     * @depends testSetPassword
     * @depends testRoleAdd
     */
    public function testSaveRoutes($token, $roleId)
    {
        $routeId = Mysql::table('route')->insert(
            ['method' => 'POST', 'uri' => 'saveAccess', 'resource' => '角色', 'action' => '设置权限']
        );
        $this->post('admin/saveAccess', ['id' => $roleId, 'route_ids' => [$routeId]], $token)
            ->assertStatus(401);
        Mysql::table('role_route')->insert(['role_id' => $roleId, 'route_id' => $routeId]);
        $this->post('admin/saveAccess', ['id' => $roleId, 'route_ids' => [$routeId]], $token)
            ->assertOk();
    }

    /**
     * @depends testAdminLogin
     * @depends testAdd
     * @depends testRoleAdd
     */
    public function testDel($token, $id, $roleId)
    {
        $this->delete('admin/role', ['id' => $roleId], $token)->assertArrayHasKey('error');
        $this->delete('admin/admin', ['id' => $id], $token);
        $this->delete('admin/role', ['id' => $roleId], $token)->assertArrayHasKey('msg');
    }
}
