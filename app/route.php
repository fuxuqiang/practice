<?php

use src\Route;

Route::add([
    'POST' => [
        'auth/sendCode' => 'Auth@sendCode',
        'register' => 'Auth@register',
        'login' => 'Auth@userLogin'
    ],
    'GET' => ['regions' => 'Region@index']
]);

Route::prefix('user')->auth(\app\auth\User::class)->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'update' => 'User@update',
        'tradeNote' => 'User@updateTradeNote',
        'phone' => 'Auth@changePhone'
    ],
    'POST' => ['trade' => 'User@trade', 'address' => 'User@addAddress'],
    'GET' => [
        'trades' => 'User@getTrades',
        'addresses' => 'User@addresses'
    ]
]);

Route::prefix('admin')->add(['POST' => ['login' => 'Auth@adminLogin']]);

Route::prefix('admin')->auth(\app\auth\Admin::class)->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'phone' => 'Auth@changePhone',
        'adminName' => 'Admin@update',
        'adminRole' => 'Admin@setRole',
        'role' => 'Role@update',
        'route' => 'Route@update',
    ],
    'POST' => [
        'admin' => 'Admin@create',
        'route' => 'Route@create',
        'role' => 'Role@create',
        'saveAccess' => 'Role@saveRoute',
    ],
    'GET' => [
        'admins' => 'Admin@index',
        'routes' => 'Route@index',
        'roles' => 'Role@index',
    ],
    'DELETE' => [
        'admin' => 'Admin@delete',
        'role' => 'Role@delete',
        'route' => 'Route@delete',
    ],
]);