<?php
namespace app\command;

use src\Http;

class Register
{
    public function handle()
    {
        $phones = Http::request(function ($mh) {
            for ($i=0; $i < 20; $i++) { 
                $phone = mt_rand(12000000000, 19999999999);
                $phones[] = $phone;
                Http::addCurl($mh, 'http://stock.test/sendCode', ['phone' => $phone]);
            }
            return $phones;
        });

        Http::request(function ($mh) use ($phones) {
            foreach ($phones as $phone) {
                Http::addCurl($mh, 'http://stock.test/register', [
                    'phone' => $phone,
                    'code' => \src\Container::get('Redis')->get($phone)
                ]);
            }
        });
    }
}
