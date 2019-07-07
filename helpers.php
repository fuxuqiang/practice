<?php

function input()
{
    ($input = $_REQUEST) || parse_str(file_get_contents('php://input'), $input);
    return $input;
}

function redis()
{
    static $redis;
    if (!$redis) {
        $redis = new Redis;
        $redis->connect('127.0.0.1');
    }
    return $redis;
}

function response(int $code, string $msg = '')
{
    http_response_code($code);
    die($msg);
}

function auth(\src\Mysql $user = null)
{
    static $boundUser;
    $user && $boundUser = $user;
    return $boundUser;
}