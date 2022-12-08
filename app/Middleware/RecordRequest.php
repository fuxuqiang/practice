<?php

namespace App\Middleware;

class RecordRequest
{
    public function handle(\Fuxuqiang\Framework\Request $request)
    {
        if (isset($request->server['REMOTE_ADDR'])) {
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
            \Src\Mysql::table('request_log')->insert($data + ['key' => md5(json_encode($data))]);
        }
    }
}
