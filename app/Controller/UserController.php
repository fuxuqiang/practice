<?php
namespace App\Controller;

use App\Model\User;
use Fuxuqiang\Framework\{JWT, Request, ResponseCode, ResponseException, Route\Route};
use Src\{Mysql, Redis};

#[Route(middlewares:[\App\Middleware\RequestRecorder::class])]
class UserController
{
    /**
     * @throws ResponseException
     */
    #[Route('sendCode', 'POST')]
    public function sendCode(int $mobile, Request $request): void
    {
        $server = $request->server;
        $isFrequently = Mysql::table('request_log')
            ->whereBetween('created_at', [$server['REQUEST_TIME'] - 60, $server['REQUEST_TIME'] - 1])
            ->where('ip', $server['REMOTE_ADDR'])
            ->exists('uri', 'sendCode');
        if ($isFrequently) {
            throw new ResponseException('请勿频繁发送验证码', ResponseCode::BadRequest);
        }
        Redis::setex($mobile, 99, mt_rand(1000, 9999));
    }

    #[Route('login', 'POST')]
    public function login(string $mobile, int $code, JWT $jwt, Request $request): array
    {
        if (($existingCode = Redis::get($mobile)) && $existingCode == $code) {
            $userId = User::where(User::MOBILE, $mobile)->value(User::ID);
            if (!$userId) {
                $user = new User;
                $user->mobile = $mobile;
                $user->createdAt = $request->server['REQUEST_TIME'];
                $user->save();
                $userId = $user->id;
            }
            return data($jwt->encode($userId));
        }
        return error('验证码错误');
    }
}
