<?php
namespace App\Controller;

use Fuxuqiang\Framework\{Request, ResponseException, Route\Route};
use Src\{Mysql, Redis};

#[Route(middlewares:[\App\Middleware\RecordRequest::class])]
class UserController
{
    #[Route('sendCode', 'POST')]
    public function sendCode(int $mobile, Request $request)
    {
        $server = $request->server;
        $isFrequently = Mysql::table('request_log')
            ->whereBetween('created_at', [$server['REQUEST_TIME'] - 60, $server['REQUEST_TIME'] - 1])
            ->where('ip', $server['REMOTE_ADDR'])
            ->exists('uri', 'sendCode');
        if ($isFrequently) {
            throw new ResponseException('请勿频繁发送验证码', ResponseException::BAD_REQUEST);
        }
        Redis::setex($mobile, 99, mt_rand(1000, 9999));
    }
}
