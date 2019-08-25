<?php

use src\Route;

Route::add([
    'POST' => [
        'auth/sendCode' => 'Auth@sendCode',
        'register' => 'Auth@register',
        'login' => 'Auth@userLogin'
    ],
    'GET' => ['regions' => 'Region@list']
]);

$route = Route::prefix('user')->auth(\app\auth\User::class);

$route->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'update' => 'User@update',
        'tradeNote' => 'User@updateTradeNote',
        'phone' => 'Auth@changePhone'
    ],
    'POST' => ['trade' => 'User@trade'],
    'GET' => [
        'trades' => 'User@getTrades',
        'addresses' => 'Address@list'
    ]
]);

$route->resource('address', ['add', 'update', 'del']);

Route::prefix('admin')->add(['POST' => ['login' => 'Auth@adminLogin']]);

$route = Route::prefix('admin')->auth(\app\auth\Admin::class);

$route->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'phone' => 'Auth@changePhone',
        'adminName' => 'Admin@update',
        'adminRole' => 'Admin@setRole',
    ],
    'POST' => [
        'saveAccess' => 'Role@saveRoute',
    ]
]);

$route->resource('role', ['add', 'update', 'del', 'list']);
$route->resource('route', ['add', 'update', 'del', 'list']);
$route->resource('admin', ['add', 'del', 'list']);