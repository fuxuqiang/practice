<?php
namespace app\command;

use src\Mysql;
use vendor\HttpClient;

class Address
{
    public function handle()
    {
        $mobiles = Mysql::table('user')->rand(16)->all('mobile');
        $addresses = Mysql::table('region')->rand(16)->col('code');
        $http = new HttpClient;
        foreach (\app\model\Login::getToken($mobiles) as $key => $token) {
            $http->request(
                'http://practice.test/user/address',
                ['code' => $addresses[$key]],
                [CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token['token']]]
            );
        }
    }
}
