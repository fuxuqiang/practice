<?php
namespace app\controller;

use app\model\Auth;

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
        validateCode($phone, $code);
        return Auth::addUser($phone);
    }

    /**
     * 用户登录
     */
    public function userLogin(int $phone)
    {
        if (empty($_POST['password']) && empty($_POST['code'])) {
            return ['error' => '参数错误'];
        }
        if (! $user = mysql('user')->where('phone', $phone)->get('id', 'password')->fetch_object()) {
            return Auth::addUser($phone);
        }
        if (isset($_POST['code'])) {
            validateCode($phone, $_POST['code']);
        } elseif (!password_verify($_POST['password'], $user->password)) {
            return ['error' => '密码错误'];
        }
        return ['data' => Auth::getToken('user', $user->id)];
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
            if (! $admin = mysql()->query(
                'SELECT `a`.`id`,`a`.`password`,`r`.`pid` FROM `admin` `a`
                LEFT JOIN `role` `r` ON `r`.`id`=`a`.`role_id` WHERE `a`.`phone`=?',
                [$phone]
            )->fetch_object()) {
                return ['error' => '用户不存在']; 
            }
            if (!$admin->pid) {
                return ['error' => '请输入验证码'];
            }
            if (!password_verify($_POST['password'], $admin->password)) {
                return ['error' => '密码错误'];
            }
        } else {
            if (! $admin = mysql('admin')->where('phone', $phone)->get('id')->fetch_object()) {
                return ['error' => '用户不存在'];
            }
            validateCode($phone, $_POST['code']);  
        }
        return ['data' => Auth::getToken('admin', $admin->id)];
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

    /**
     * 换绑手机
     */
    public function changePhone(int $phone, int $code)
    {
        validateCode($phone, $code);
        $auth = auth();
        return Auth::registerPhone($auth->getTable(), $phone, function () use ($auth, $phone) {
            $auth->update(['phone' => $phone]);
        }, ['msg' => '换绑成功']);
    }
}