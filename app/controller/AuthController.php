<?php

namespace app\controller;

use src\{Mysql, Redis};
use vendor\{JWT, Request};

class AuthController
{
    /**
     * 发送验证码
     */
    public function sendCode($mobile)
    {
        if (
            !isset($_SERVER['REMOTE_ADDR'])
            || !Mysql::table('request_log')->whereBetween(
                'created_at',
                [timestamp($_SERVER['REQUEST_TIME'] - 60), timestamp($_SERVER['REQUEST_TIME'] - 1)]
            )->where('ip', $_SERVER['REMOTE_ADDR'])->exists('uri', 'send_code')
        ) {
            if (Redis::setex($mobile, 99, mt_rand(1000, 9999))) {
                return ['msg' => '发送成功'];
            }
        }
    }

    /**
     * 用户登录
     */
    public function userLogin($mobile, JWT $jwt, Request $request)
    {
        $input = $request->get();

        if (empty($input['password']) && empty($input['code'])) {
            return error('参数错误');
        }

        $user = Mysql::table('user')->cols('id', 'password')->where('mobile', $mobile)->get();

        if (empty($input['password'])) {
            validateCode($mobile, $input['code']);
            if (!$user) {
                return [
                    'data' => $jwt->encode(Mysql::table('user')->insert(['mobile' => $mobile]), 'user'),
                    'msg' => '注册成功'
                ];
            }
        } elseif (!password_verify($input['password'], $user->password)) {
            return error('密码错误');
        }

        return ['data' => $jwt->encode($user->id, 'user' . $user->password)];
    }

    /**
     * 管理员登录
     */
    public function adminLogin($mobile, JWT $jwt, Request $request)
    {
        $input = $request->get();

        if (empty($input['code'])) {
            if (empty($input['password'])) {
                return error('参数错误');
            }
            if (
                !$admin = Mysql::query(
                    'SELECT `a`.`id`,`a`.`password`,`r`.`pid` FROM `admin` `a`
                    LEFT JOIN `role` `r` ON `r`.`id`=`a`.`role_id` WHERE `a`.`mobile`=?',
                    [$mobile]
                )->fetch_object()
            ) {
                return error('用户不存在');
            }
            if (!$admin->pid) {
                return error('请输入验证码');
            }
            if (!password_verify($input['password'], $admin->password)) {
                return error('密码错误');
            }
        } else {
            if (!$admin = Mysql::table('admin')->cols('id', 'password')->where('mobile', $mobile)->get()) {
                return error('用户不存在');
            }
            validateCode($mobile, $input['code']);
        }

        return ['data' => $jwt->encode($admin->id, 'admin' . $admin->password)];
    }

    /**
     * 设置密码
     */
    public function setPassword($password, Request $request)
    {
        if (!preg_match('/^(?!\d+$)(?![a-zA-Z]+$)[\dA-Za-z]{6,10}$/', $password)) {
            return error('密码长度至少为6位，由数字和字母组成');
        }
        $request->user()->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);
        return ['msg' => '修改成功'];
    }

    /**
     * 换绑手机
     */
    public function changeMobile(Request $request, $code)
    {
        $request->validate(['mobile' => 'unique:user,mobile']);
        $mobile = $request->mobile;
        validateCode($mobile, $code);
        $request->user()->update(['mobile' => $mobile]);
        return ['msg' => '换绑成功'];
    }

    /**
     * 商家认证
     */
    public function registerMerchant(Request $request)
    {
        Mysql::begin();
        try {
            Mysql::table('user_merchant')->insert([
                'user_id' => $request->userId(),
                'merchant_id' => Mysql::table('merchant')->insert($request->get('name', 'credit_code'))
            ]);
            Mysql::commit();
        } catch (\Exception $e) {
            Mysql::rollback();
            return error('提交失败');
        }
        return ['msg' => '提交成功'];
    }
}
