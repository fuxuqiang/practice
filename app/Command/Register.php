<?php

namespace App\Command;

class Register
{
    public function handle()
    {
        for ($i = 0; $i < 4; $i++) {
            $mobile = mt_rand(12000000000, 19999999999);
            $mobiles[] = ['mobile' => $mobile];
        }

        iterator_count(\App\Model\Login::getToken($mobiles));
    }
}
