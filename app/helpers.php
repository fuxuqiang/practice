<?php

function validateCode($phone, $code)
{
    sessionStart();
    if ($code != $_SESSION['code_' . $phone]) {
        throw new Exception('验证码错误', 200);
    }
    unset($_SESSION['code_' . $phone]);
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
