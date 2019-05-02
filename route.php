<?php

return [
    'unauth' => [
        'POST' => [
            'auth/sendCode' => 'Auth/sendCode',
            'register' => 'Auth/register',
            'login' => 'Auth/login'
        ],
        'GET' => [
            'analyze' => 'Analyze/index'
        ]
    ],
    'auth' => [
        'PUT' => [
            'user/setPassword' => 'User/setPassword',
            'user/update' => 'User/update'
        ],
        'POST' => [
            'user/trade' => 'User/trade'
        ],
        'GET' => [
            'user/trades' => 'User/getTrades'
        ]
    ]
];