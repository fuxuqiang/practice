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

Route::prefix('user')->auth(\auth\User::class)->add([
    'PUT' => [
        'setPassword' => 'Auth@setPassword',
        'update' => 'User@update',
        'updateTradeNote' => 'User@updateTradeNote',
        'changePhone' => 'Auth@changePhone'
    ],
    'POST' => ['trade' => 'User@trade'],
    'GET' => ['trades' => 'User@getTrades']
]);

Route::prefix('admin')->add(['POST' => ['login' => 'Auth@adminLogin']]);

Route::prefix('admin')->auth(\auth\Admin::class)->add([
    'PUT' => [
        'setPassword' => 'Auth@setPassword',
        'changePhone' => 'Auth@changePhone',
        'updateProfile' => 'Admin@update',
        'setRole' => 'Admin@setRole',
    ],
    'POST' => [
        'createAdmin' => 'Admin@create',
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