<?php

namespace App\Middleware;

use Fuxuqiang\Framework\{Request, ResponseException};

class RequestRecorder
{
    public function handle(Request $request): void
    {
        $input = $request->getData();
        ksort($input);
        $log = new \App\Model\RequestLog;
        $log->method = $request->server['REQUEST_METHOD'];
        $log->uri = $request->uri;
        $log->ip = $request->server['REMOTE_ADDR'];
        $log->input = json_encode($input);
        $log->token = $request->token();
        $log->createdAt = $request->server['REQUEST_TIME'];
        $log->key = md5(json_encode($log));
        try {
            $log->save();
        } catch (\Throwable) {
            throw new ResponseException('请勿频繁请求');
        }
    }
}
