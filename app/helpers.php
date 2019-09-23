<?php

function validateCode($phone, $code)
{
    ($code != \src\Container::get('Redis')->get($phone)) && response(200, '验证码错误');
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