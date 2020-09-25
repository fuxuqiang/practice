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

    public function request($uri, $params = [], $method = 'GET')
    {
        return json_decode($this->requestGetRaw($uri, $params, $method));
    }

    public function requestGetRaw($uri, $params = [], $method = 'GET', $withToken = true)
    {
        $headers = ['api-version: v1', 'Content-Type: application/json'];
        if ($withToken && $token = $this->getTokenData()) {
            array_push($headers, 'Authorization: Bearer' . $token->accessToken);
        }
        return (new \Fuxuqiang\Framework\HttpClient)->request(
            'https://yd.yunding360.com/openapi/' . $uri,
            json_encode($params),
            [CURLOPT_HTTPHEADER => $headers],
            $method
        );
    }

    public function getTokenData()
    {
        return file_exists($this->tokenFile) ? json_decode(file_get_contents($this->tokenFile)) : null;
    }
}
