<?php

namespace app\command;

class Register
{
    public function handle()
    {
        for ($i = 0; $i < 4; $i++) {
            $phone = mt_rand(12000000000, 19999999999);
            $phones[] = ['phone' => $phone];
        }

        iterator_count(\app\model\Login::getToken($phones));
    }
}
