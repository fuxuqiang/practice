<?php
namespace app\model;

use src\Http;

class Login 
{
    public static function getToken($phones)
    {
        Http::request(function ($mh) use ($phones) {
            foreach ($phones as $phone) {
                Http::addCurl($mh, 'http://stock.test/sendCode', ['phone' => $phone['phone']]);
            }
        });

        return array_map(function ($phone) {
            $ch = Http::getHandler('http://stock.test/login', [
                'phone' => $phone['phone'],
                'code' => \vendor\Container::get('Redis')->get($phone['phone'])
            ]);
            unset($phone['phone']);
            return ['token' => json_decode(curl_exec($ch))->data] + $phone;
        }, $phones);
    }
}
