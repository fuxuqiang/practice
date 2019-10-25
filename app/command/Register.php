<?php
namespace app\command;

use src\HttpClient;

class Register
{
    public function handle()
    {
        $phones = HttpClient::request(function ($mh) {
            for ($i=0; $i < 20; $i++) { 
                $phone = mt_rand(12000000000, 19999999999);
                $phones[] = $phone;
                HttpClient::addCurl($mh, 'http://stock.test/sendCode', ['phone' => $phone]);
            }
            return $phones;
        });

        HttpClient::request(function ($mh) use ($phones) {
            foreach ($phones as $phone) {
                HttpClient::addCurl($mh, 'http://stock.test/register', [
                    'phone' => $phone,
                    'code' => \src\Container::get('Redis')->get($phone)
                ]);
            }
        });
    }
}
