<?php

namespace app\command\yunding;

class RefreshYundingToken
{
    public function handle($type, \app\model\Yunding $yunding)
    {
        $account = env('yunding');
        if ($type == 'init') {
            $data = $yunding->requestGetRaw('oauth/token', [
                'account' => $account['account'],
                'enterpriseCode' => $account['enterprise_code'],
                'password' => $account['password']
            ]);
        } else {
            $data = $yunding->requestGetRaw('oauth/token/' . $yunding->getTokenData()->refreshToken);
        }
        file_put_contents(runtimePath($yunding->tokenFile), $data);
    }
}
