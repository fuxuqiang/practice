<?php

function validateCode($phone, $code)
{
    if ($code != \src\Container::get('Redis')->get($phone)) {
        throw new Exception('验证码错误', 200);
    }
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