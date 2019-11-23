<?php
namespace app\command;

use vendor\HttpClient;

class Address
{
    public function handle()
    {
        $phones = mysql('user')->rand(16)->all('phone');

        $users = \app\model\Login::getToken($phones);

        HttpClient::request(function ($mh) use ($users) {
            $addresses = mysql('address')->rand(16)->col('code');
            foreach ($users as $key => $token) {
                $ch = HttpClient::getHandler(
                    'http://stock.test/user/address',
                    ['code' => $addresses[$key], 'address' => '']
                );
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token['token']]);
                curl_multi_add_handle($mh, $ch);
            }
        });
    }
}
