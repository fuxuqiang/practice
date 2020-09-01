<?php

namespace app\model;

class Yunding
{
    public $tokenFile, $storesFile, $customersFile;

    public function __construct()
    {
        $this->tokenFile = runtimePath('yunding_token');
        $this->storesFile = runtimePath('yunding_stores');
        $this->customersFile = runtimePath('yunding_customers');
    }

    public function request($uri, $params = [], $method = 'GET')
    {
        return json_decode($this->requestGetRaw($uri, $params, $method));
    }

    public function requestGetRaw($uri, $params = [], $method = 'POST')
    {
        return (new \vendor\HttpClient)->request(
            'https://yd.yunding360.com/openapi/' . $uri,
            json_encode($params),
            [
                CURLOPT_HTTPHEADER => array_merge(
                    ['api-version: v1', 'Content-Type: application/json'],
                    ($token = $this->getTokenData()) ? ['Authorization: Bearer' . $token->accessToken] : []
                )
            ],
            $method
        );
    }

    public function getTokenData()
    {
        return file_exists($this->tokenFile) ? json_decode(file_get_contents($this->tokenFile)) : null;
    }
}
