<?php

namespace App\Model;

class RequestLog extends \Fuxuqiang\Framework\Model\Model
{
    protected string $primaryKey = 'key';

    const CREATED_AT = 'created_at',
        IP = 'ip',
        URI = 'uri';

    public string $key, $method, $uri, $ip, $input, $createdAt;

    public ?string $token;
}