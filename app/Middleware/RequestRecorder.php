<?php

namespace App\Middleware;

use Fuxuqiang\Framework\{Request, ResponseCode, ResponseException};

class RequestRecorder
{
    public function handle(Request $request): void
    {
        $input = $request->getData();
        ksort($input);
        $data = [
            'method' => $request->server['REQUEST_METHOD'],
            'uri' => $request->uri,
            'ip' => $request->server['REMOTE_ADDR'],
            'input' => json_encode($input),
            'token' => $request->token() ?: '',
            'created_at' => $request->server['REQUEST_TIME']
        ];
        try {
            \Src\Mysql::table('request_log')->insert($data + ['key' => md5(json_encode($data))]);
        } catch (\Throwable $th) {
            throw new ResponseException('请勿频繁请求', ResponseCode::BadRequest);
        }
    }
}
