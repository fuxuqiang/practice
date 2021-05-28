<?php

namespace App\Model;

class Yunding
{
    public $tokenFile, $storesFile, $customersFile;

    public function __construct()
    {
        $this->tokenFile = runtimePath('yunding_token');
        $this->storesFile = runtimePath('yunding_stores');
        $this->customersFile = runtimePath('yunding_customers');
    }

    /**
     * 请求接口
     */
    public function request($uri, $params = [], $method = 'GET')
    {
        return json_decode($this->requestGetRaw($uri, $params, $method));
    }

    /**
     * 请求接口获取原始响应数据
     */
    public function requestGetRaw($uri, $params = [], $method = 'GET', $withToken = true)
    {
        $headers = ['api-version: v1', 'Content-Type: application/json'];
        if ($withToken && file_exists($this->tokenFile)) {
            $headers[] = $this->getHeader(file_get_contents($this->tokenFile));
        }
        try {
            return $this->query($uri, $params, $headers, $method);
        } catch (\Exception $e) {
            if ($e->getCode() == 401) {
                $account = env('yunding');
                $token = $this->requestGetRaw(
                    'oauth/token',
                    [
                        'account' => $account['account'],
                        'enterpriseCode' => $account['enterprise_code'],
                        'password' => $account['password']
                    ],
                    'POST',
                    false
                );
                file_put_contents($this->tokenFile, $token);
                $headers[2] = $this->getHeader($token);
                return $this->query($uri, $params, $headers, $method);
            }
            throw $e;
        }
    }

    /**
     * 请求云盯接口
     */
    private function query($uri, $params, $headers, $method)
    {
        return (new \Fuxuqiang\Framework\HttpClient)->request(
            'https://yd.yunding360.com/openapi/' . $uri,
            json_encode($params),
            [CURLOPT_HTTPHEADER => $headers],
            $method
        );
    }

    /**
     * 获取请求头
     */
    private function getHeader($data)
    {
        return 'Authorization: Bearer' . json_decode($data)->accessToken;
    }
}
