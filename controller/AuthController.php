<?php
namespace controller;

use src\Mysql;
use model\User;
use model\Auth;

class AuthController
{
    /**
     * 发送验证码
     */
    public function sendCode(int $phone)
    {
        if (!preg_match('/1[3-9]\d{9}/', $phone)) {
            return ['error' => '手机格式错误'];
        }
        $code = mt_rand(1000, 9999);
        if (true/* todo 发送验证码到手机 */) {
            redis()->setex($phone, 99, $code);
        }
        return ['msg' => '发送成功'];
    }

    /**
     * 用户注册
     */
    public function register(int $phone, int $code)
    {
        if ($code != redis()->get($phone)) {
            return ['error' => '验证码错误'];
        }
        return Auth::register('user', $phone);
    }

    /**
     * 用户登录
     */
    public function userLogin(int $phone)
    {
        if (empty($_POST['password']) && empty($_POST['code'])) {
            return ['error' => '参数错误'];
        }
        if (! $row = Mysql::query('SELECT `id`,`password` FROM `user` WHERE `phone`=?', 'i', [$phone])->fetch_assoc()) {
            return Auth::register($table, $phone);
        }
        if (isset($_POST['code'])) {
            if ($_POST['code'] != redis()->get($phone)) {
                return ['error' => '验证码错误'];
            }
        } elseif (!password_verify($_POST['password'], $row['password'])) {
            return ['error' => '密码错误'];
        }
        return ['data' => Auth::getToken('user', $row['id'])];
    }

    /**
     * 管理员登录
     */
    public function adminLogin(int $phone)
    {
        if (empty($_POST['code'])) {
            if (empty($_POST['password'])) {
                return ['error' => '参数错误'];
            }
            if (! $row = Mysql::query(
                'SELECT `a`.`id`,`a`.`password`,`r`.`is_super` FROM `admin` `a`
                LEFT JOIN `role` `r` ON `r`.`id`=`a`.`role_id` WHERE `a`.`phone`=?',
                'i',
                [$phone]
            )->fetch_assoc()) {
                return ['error' => '用户不存在']; 
            }
            if ($row['is_super']) {
                return ['error' => '请输入验证码'];
            }
            if (!password_verify($_POST['password'], $row['password'])) {
                return ['error' => '密码错误'];
            }
        } else {
            if (! $row = Mysql::query('SELECT `id`,`password` FROM `admin` WHERE `phone`=?', 'i', [$phone])->fetch_assoc()) {
                return ['error' => '用户不存在'];
            }
            if ($_POST['code'] != redis()->get($phone)) {
                return ['error' => '验证码错误'];
            }    
        }
        return ['data' => Auth::getToken('admin', $row['id'])];
    }

    /**
     * 设置密码
     */
    public function setPassword($password)
    {
        if (!preg_match('/^(?!\d+$)(?![a-zA-Z]+$)[\dA-Za-z]{6,}$/', $password)) {
            return ['error' => '密码长度至少为6位，由数字和字母组成'];
        }
        auth()->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);
        return ['msg' => '修改成功'];
    }
}