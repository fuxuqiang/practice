<?php

return [
    // 允许跨域的地址
    'cors' => 'http://127.0.0.1',
    // mysql配置
    'mysql' => [
        'host' => '127.0.0.1',
        'user' => 'guest',
        'pwd' => 'eb',
        'name' => 'personal'
    ],
    // reids配置
    'redis' => [
        'host' => '127.0.0.1',
    ],
    // jwt配置
    'jwt' => [
        'id' => '5d332151a77de',
        'exp' => 36000
    ],
    // 默认每页行数
    'per_page' => 5
];