<?php

use vendor\Route;

// 前台

Route::add([
    'POST' => [
        'sendCode' => 'Auth@sendCode',
        'login' => 'Auth@userLogin'
    ],
    'GET' => ['regions' => 'Region@list']
]);

$route = Route::auth(\app\auth\User::class);

$route->add([
    'POST' => ['order' => 'Order@add'],
    'GET' => ['order' => 'Order@info']
]);

$route = $route->prefix('user');

$route->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        '' => 'User@update',
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

// 后台

$adminRoute = Route::prefix('admin');

$adminRoute->add(['POST' => ['login' => 'Auth@adminLogin']]);

$route = $adminRoute->auth(\app\auth\Admin::class);

$route->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'phone' => 'Auth@changePhone',
        'adminName' => 'Admin@update',
        'adminRole' => 'Admin@setRole',
    ],
    'POST' => ['saveAccess' => 'Role@saveRoutes'],
    'GET' => ['routes' => 'Role@listRoutes'],
]);

$route->resource('role', ['add', 'update', 'del', 'list']);
$route->resource('admin', ['add', 'del', 'list']);
$route->resource('sku', ['add', 'list', 'update', 'del']);
