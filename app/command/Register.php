<?php
namespace app\command;

class Register
{
    public function handle()
    {
        $phones = $this->request(function ($mh) {
            for ($i=0; $i < 20; $i++) { 
                $phone = mt_rand(13000000000, 19999999999);
                $phones[] = $phone;
                $this->addCurl($mh, 'http://stock.test/auth/sendCode', ['phone' => $phone]);
            }
            return $phones;
        });

        $this->request(function ($mh) use ($phones) {
            foreach ($phones as $phone) {
                $this->addCurl($mh, 'http://stock.test/register', [
                    'phone' => $phone,
                    'code' => redis()->get($phone)
                ]);
            }
        });
    }

    private function request(callable $callback)
    {
        $mh = curl_multi_init();
        $rst = $callback($mh);
        $active = null;
        do {
            curl_multi_exec($mh, $active);
            usleep(10000);
        } while ($active);
        return $rst;
    }

    private function addCurl($mh, $url, $params)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($mh, $ch);
    }
}
