<?php
namespace App\Command;

use Src\Mysql;

class Address
{
    public function handle()
    {
        $mobiles = Mysql::table('user')->rand(16)->all('mobile');
        $addresses = Mysql::table('region')->rand(16)->col('code');
        $http = new \Fuxuqiang\Framework\Http\HttpClient;
        foreach (\App\Model\Login::getToken($mobiles) as $key => $token) {
            $http->request(
                'http://practice.test/user/address',
                ['code' => $addresses[$key]],
                [CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token['token']]]
            );
        }
    }
}
