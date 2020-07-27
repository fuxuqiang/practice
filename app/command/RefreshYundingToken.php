<?php

namespace app\command;

use app\model\Yunding;

class RefreshYundingToken
{
    public function handle($type)
    {
        $account = env('yunding');
        if ($type == 'init') {
            $data = Yunding::request(
                'oauth/token',
                ['account' => $account['account'], 'enterpriseCode' => $account['enterprise_code'], 'password' => $account['password']],
                'POST'
            );
        } else {
            $data = Yunding::request('oauth/token/' . Yunding::getTokenData()->refreshToken, [], 'POST');
        }
        file_put_contents(Yunding::TOKEN_FILE, $data);
    }
}
