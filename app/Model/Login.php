<?php

namespace App\Model;

use Fuxuqiang\Framework\HttpClient;

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
            $response = json_decode(
                $login->request(
                    'http://practice.test/login',
                    ['code' => \Src\Redis::get($val['params']['mobile'])] + $val['params'],
                )
            );
            if (isset($response->data)) {
                yield ['token' => $response->data] + $val['params'];
            }
        }
    }
}
