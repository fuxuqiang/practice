<?php
namespace app\model;

use vendor\HttpClient;

class Login 
{
    public static function getToken($phones)
    {
        $sendCode = new HttpClient(true);
        foreach ($phones as $phone) {
            $sendCode->addHandle('http://practice.test/sendCode', $phone, [CURLOPT_HEADER => true], 'POST');
        }

        $redis = new \Redis;
        $redis->connect('127.0.0.1');
        $login = new HttpClient;
        foreach ($sendCode->multiRequest() as $val) {
            preg_match('/Set-Cookie:\s(PHPSESSID=.+);/', curl_multi_getcontent($val['handle']), $matches);
            $response = json_decode(
                $login->request(
                    'http://practice.test/login',
                    ['code' => $redis->get($val['params']['phone'])] + $val['params'],
                    [CURLOPT_COOKIE => $matches[1]]
                )
            );
            if (isset($response->data)) {
                yield ['token' => $response->data] + $val['params'];
            }
        }
    }
}
