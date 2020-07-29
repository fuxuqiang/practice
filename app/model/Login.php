<?php

namespace app\model;

use vendor\HttpClient;

class Login 
{
    public static function getToken($mobiles)
    {
        $sendCode = new HttpClient(true);
        foreach ($mobiles as $mobile) {
            $sendCode->addHandle('http://practice.test/send_code', $mobile, [CURLOPT_HEADER => true], 'POST');
        }

        $login = new HttpClient;
        foreach ($sendCode->multiRequest() as $val) {
            preg_match('/Set-Cookie:\s(PHPSESSID=.+);/', curl_multi_getcontent($val['handle']), $matches);
            $response = json_decode(
                $login->request(
                    'http://practice.test/login',
                    ['code' => \src\Redis::get($val['params']['mobile'])] + $val['params'],
                    [CURLOPT_COOKIE => $matches[1]]
                )
            );
            if (isset($response->data)) {
                yield ['token' => $response->data] + $val['params'];
            }
        }
    }
}
