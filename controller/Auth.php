<?php
namespace controller;

use src\Mysql;
use model\User;
use model\Register;

class Auth
{
    /**
     * 发送验证码
     */
    public function sendCode()
    {
        checkInput(['phone']);
        $code = mt_rand(1000, 9999);
        if (true/* todo 发送验证码到手机 */) {
            redis()->setex($_POST['phone'], 99, $code);
        }
        return ['msg' => '发送成功'];
    }

    /**
     * 注册
     */
    public function register()
    {
        checkInput(['phone', 'code']);
        if ($_POST['code'] != redis()->get($_POST['phone'])) {
            return ['error' => '验证码错误'];
        }
        return Register::handle('user', $_POST['phone']);
    }

    /**
     * 用户登录
     */
    public function userLogin()
    {
        checkInput(['phone']);
        if (empty($_POST['password']) && empty($_POST['code'])) {
            return ['error' => '参数错误'];
        }
        if (! $row = Mysql::query('SELECT `id`,`password` FROM `user` WHERE `phone`=?', 'i', [$_POST['phone']])->fetch_assoc()) {
            return User::add($_POST['phone']);
        }
        if (isset($_POST['code'])) {
            if ($_POST['code'] != redis()->get($_POST['phone'])) {
                return ['error' => '验证码错误'];
            }
        } elseif (!password_verify($_POST['password'], $row['password'])) {
            return ['error' => '密码错误'];
        }
        $token = uniqid();
        (new User($row['id']))->update(['api_token' => $token, 'token_expires' => date('Y-m-d H:i:s', strtotime('1 hour'))]);
        return ['data' => $token];
    }

    /**
     * 创建管理员
     */
    public function createAdmin()
    {
        checkInput(['phone', 'password']);
        return Register::handle('admin', $_POST['phone']);
    }
}