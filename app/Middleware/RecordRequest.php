<?php

namespace App\Middleware;

class RecordRequest
{
    public function handle(\Fuxuqiang\Framework\Request $request)
    {
        $server = $request->server();
        if (isset($server['REMOTE_ADDR'])) {
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
            \Src\Mysql::table('request_log')->insert($data + ['key' => md5(json_encode($data))]);
        }
    }
}
