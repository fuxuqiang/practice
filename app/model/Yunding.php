<?php

namespace app\model;

class Yunding
{
    const TOKEN_FILE = __DIR__ . '/../../runtime/yunding_token';

    public static function request($uri, $params = [], $method = 'GET')
    {
        return (new \vendor\HttpClient)->request(
            'https://yd.yunding360.com/openapi/' . $uri,
            json_encode($params),
            [
                CURLOPT_HTTPHEADER => array_merge(
                    ['api-version: v1', 'Content-Type: application/json'],
                    ($token = self::getTokenData()) ? ['Authorization: Bearer' . $token->accessToken] : []
                )
            ],
            $method
        );
    }

    public static function getTokenData()
    {
        return file_exists(self::TOKEN_FILE) ? json_decode(file_get_contents(self::TOKEN_FILE)) : null;
    }
}
