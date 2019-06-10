<?php

function checkInput(array $params = [])
{
    ($input = $_REQUEST) || parse_str(file_get_contents('php://input'), $input);
    foreach ($params as $param) {
        if (!isset($input[$param])) {
            json(['error' => '参数错误']);
        }
    }
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

function response($code)
{
    http_response_code($code);
    die;
}

function auth($boundUser = null)
{
    static $user;
    $boundUser && $user = $boundUser;
    return $user;
}