<?php

namespace App\Command\Yunding;

class RefreshYundingToken
{
    public function handle(\App\Model\Yunding $yunding)
    {
        $account = env('yunding');

        $data = $yunding->requestGetRaw(
            'oauth/token',
            [
                'account' => $account['account'],
                'enterpriseCode' => $account['enterprise_code'],
                'password' => $account['password']
            ],
            'POST',
            false
        );

        file_put_contents($yunding->tokenFile, $data);
    }
}
