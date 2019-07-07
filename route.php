<?php

use src\Route;
use auth\Auth;

Route::add([
    'POST' => [
        'auth/sendCode' => 'Auth@sendCode',
        'register' => 'Auth@register',
        'login' => 'Auth@userLogin'
    ]
]);

Route::auth(new Auth('user'))->add([
    'PUT' => [
        'user/setPassword' => 'Auth@setPassword',
        'user/update' => 'User@update',
        'user/updateTradeNote' => 'User@updateTradeNote'
    ],
    'POST' => ['user/trade' => 'User@trade'],
    'GET' => ['user/trades' => 'User@getTrades']
]);

Route::prefix('admin')->add(['POST' => ['login' => 'Auth@adminLogin']]);

Route::prefix('admin')->auth(new Auth('admin'))->add([
    'PUT' => ['setPassword' => 'Auth@setPassword'],
    'POST' => ['createAdmin' => 'Admin@create']
]);