<?php

namespace App\Model;

class Feie
{
    public function request($apiname, $params)
    {
        $time = time();
        return (new \Fuxuqiang\Framework\HttpClient)->request('https://api.feieyun.cn/Api/Open/', [
            'user' => 'chentefu@linhuiba.com',
            'stime' => $time,
            'sig' => sha1('chentefu@linhuiba.comv9TzHSw32zDITjy8' . $time),
            'apiname' => $apiname
        ] + $params);
    }
}