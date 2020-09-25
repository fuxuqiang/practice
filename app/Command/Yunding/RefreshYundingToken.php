<?php

namespace App\Command\Yunding;

class RefreshYundingToken
{
    public function handle($type, \App\Model\Yunding $yunding)
    {
        $account = env('yunding');
        if ($type == 'init') {
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
        } else {
            $data = $yunding->requestGetRaw('oauth/token/' . $yunding->getTokenData()->refreshToken);
        }
        file_put_contents($yunding->tokenFile, $data);
    }
}
