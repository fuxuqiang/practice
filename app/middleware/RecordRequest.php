<?php

namespace app\middleware;

class RecordRequest
{
    public function handle(\vendor\Request $request)
    {
        $server = $request->server();
        $input = $request->get();
        ksort($input);
        $data = [
            'method' => $server['REQUEST_METHOD'],
            'uri' => $request->uri(),
            'ip' => $server['REMOTE_ADDR'],
            'input' => json_encode($input),
            'token' => $request->token() ?: '',
            'created_at' => timestamp()
        ];
        mysql('request_log')->insert($data + ['key' => md5(json_encode($data))]);
    }
}
