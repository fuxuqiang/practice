<?php

use src\Route;

Route::add([
    'POST' => [
        'auth/sendCode' => 'Auth@sendCode',
        'register' => 'Auth@register',
        'login' => 'Auth@userLogin'
    ]
]);

Route::auth(\auth\User::class)->add([
    'PUT' => [
        'user/setPassword' => 'User@setPassword',
        'user/update' => 'User@update',
        'user/updateTradeNote' => 'User@updateTradeNote'
    ],
    'POST' => [
        'user/trade' => 'User@trade'
    ],
    'GET' => [
        'user/trades' => 'User@getTrades'
    ]
]);

Route::prefix('admin')->add([
    'POST' => [
        'login' => 'Auth@adminLogin'
    ]
]);