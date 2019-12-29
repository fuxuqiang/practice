<?php

use vendor\Route;
use app\middleware\{RecordRequest, Auth};

$route = Route::middleware(RecordRequest::class);

$route->add([
    'POST' => [
        'sendCode' => 'Auth@sendCode',
        'login' => 'Auth@userLogin'
    ],
    'GET' => ['regions' => 'Region@list']
]);

// 前台

$userRoute = $route->middleware(Auth::class, 'user');

$userRoute->add([
    'POST' => ['order' => 'Order@add'],
    'GET' => ['order' => 'Order@info']
]);

$userAuthRoute = $userRoute->prefix('user');

$userAuthRoute->add([
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

$userAuthRoute->resource('address', ['add', 'update', 'del']);

// 后台

$adminRoute = Route::prefix('admin');

$adminRoute->add(['POST' => ['login' => 'Auth@adminLogin']]);

$adminAuthRoute = $adminRoute->middleware(Auth::class, 'admin');

$adminAuthRoute->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'phone' => 'Auth@changePhone',
        'adminName' => 'Admin@update',
        'adminRole' => 'Admin@setRole',
    ],
    'POST' => ['saveAccess' => 'Role@saveRoutes'],
    'GET' => ['routes' => 'Role@listRoutes'],
]);

$adminAuthRoute->resource('role', ['add', 'update', 'del', 'list']);
$adminAuthRoute->resource('admin', ['add', 'del', 'list']);
$adminAuthRoute->resource('sku', ['add', 'list', 'update', 'del']);
