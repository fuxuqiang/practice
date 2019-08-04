<?php

function validateCode(int $phone, int $code)
{
    ($code != redis()->get($phone)) && response(200, '验证码错误');
}

function validateRoleId($roleId)
{
    mysql('role')->where('id', $roleId)->exists() || response(400, '角色不存在');
}

function inSubs($id, array $data, $aid)
{
    foreach ($data as $val) {
        if ($val['pid'] == $aid) {
            return $val['id'] == $id ? true : inSubs($id, $data, $val['id']);
        }
    }
    return false;
}