<?php

use src\Route;

Route::add([
    'POST' => [
        'auth/sendCode' => 'Auth@sendCode',
        'register' => 'Auth@register',
        'login' => 'Auth@userLogin'
    ]
]);

Route::prefix('user')->auth(\auth\User::class)->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'update' => 'User@update',
        'tradeNote' => 'User@updateTradeNote',
        'phone' => 'Auth@changePhone'
    ],
    'POST' => ['trade' => 'User@trade'],
    'GET' => ['trades' => 'User@getTrades']
]);

Route::prefix('admin')->add(['POST' => ['login' => 'Auth@adminLogin']]);

Route::prefix('admin')->auth(\auth\Admin::class)->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'phone' => 'Auth@changePhone',
        'adminName' => 'Admin@update',
        'adminRole' => 'Admin@setRole',
        'role' => 'Role@update',
    ],
    'POST' => [
        'admin' => 'Admin@create',
        'route' => 'Route@create',
        'role' => 'Role@create',
    ],
    'GET' => [
        'admins' => 'Admin@index',
        'routes' => 'Route@index',
        'roles' => 'Role@index',
    ],
    'DELETE' => ['admin' => 'Admin@delete'],
]);