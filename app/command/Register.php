<?php

namespace app\command;

class Register
{
    public function handle()
    {
        for ($i = 0; $i < 4; $i++) {
            $mobile = mt_rand(12000000000, 19999999999);
            $mobiles[] = ['mobile' => $mobile];
        }

        iterator_count(\app\model\Login::getToken($mobiles));
    }
}
