<?php

namespace app\controller;

use src\Request;
use src\JWT;

class AuthController
{
    /**
     * 发送验证码
     */
    public function sendCode($phone, \Redis $redis)
    {
        if (true/* todo 发送验证码到手机 */) {
            $code = mt_rand(1000, 9999);
            $redis->setex($phone, 99, $code);
        }
        return ['msg' => '发送成功'];
    }

    /**
     * 用户登录
     */
    public function userLogin($phone, JWT $jwt, Request $request)
    {
        $input = $request->get();
        if (empty($input['password']) && empty($input['code'])) {
            return ['error' => '参数错误'];
        }
        if (!$user = mysql('user')->cols('id', 'password')
            ->where('phone', $phone)->get()) {
            return [
                'data' => $jwt->encode(mysql('user')->insert(['phone' => $phone])),
                'msg' => '注册成功'
            ];
        }
        if (isset($input['code'])) {
            validateCode($phone, $input['code']);
        } elseif (!password_verify($input['password'], $user->password)) {
            return ['error' => '密码错误'];
        }
        return ['data' => $jwt->encode($user->id)];
    }

    /**
     * 管理员登录
     */
    public function adminLogin($phone, JWT $jwt, Request $request)
    {
        $input = $request->get();
        if (empty($input['code'])) {
            if (empty($input['password'])) {
                return ['error' => '参数错误'];
            }
            if (!$admin = mysql()->query(
                'SELECT `a`.`id`,`a`.`password`,`r`.`pid` FROM `admin` `a`
                LEFT JOIN `role` `r` ON `r`.`id`=`a`.`role_id` WHERE `a`.`phone`=?',
                [$phone]
            )->fetch_object()) {
                return ['error' => '用户不存在'];
            }
            if (!$admin->pid) {
                return ['error' => '请输入验证码'];
            }
            if (!password_verify($input['password'], $admin->password)) {
                return ['error' => '密码错误'];
            }
        } else {
            if (!$admin = mysql('admin')->cols('id')->where('phone', $phone)->get()) {
                return ['error' => '用户不存在'];
            }
            validateCode($phone, $input['code']);
        }
        return ['data' => $jwt->encode($admin->id)];
    }

    /**
     * 设置密码
     */
    public function setPassword($password, Request $request)
    {
        if (!preg_match('/^(?!\d+$)(?![a-zA-Z]+$)[\dA-Za-z]{6,}$/', $password)) {
            return ['error' => '密码长度至少为6位，由数字和字母组成'];
        }
        $request->user()->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);
        return ['msg' => '修改成功'];
    }

    /**
     * 换绑手机
     */
    public function changePhone(Request $request, $code)
    {
        $request->validate(['phone' => 'unique:user,phone']);
        $phone = $request->phone;
        validateCode($phone, $code);
        $request->user()->update(['phone' => $phone]);
        return ['msg' => '换绑成功'];
    }
}
