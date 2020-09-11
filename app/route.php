<?php

use Fuxuqiang\Framework\Route;
use App\Middleware\{RecordRequest, Auth};

$route = Route::middleware(RecordRequest::class);

$route->add([
    'POST' => [
        'send_code' => 'Auth@sendCode',
        'login' => 'Auth@userLogin'
    ],
    'GET' => [
        'regions' => 'Region@list',
        'get_region_code' => 'Region@getCode'
    ]
]);

$route->prefix('yunding')->add([
    'GET' => [
        'stores' => 'Yunding@getStores',
        'store' => 'Yunding@getStore',
        'customer_flow' => 'Yunding@getCustomerFlow',
        'devices' => 'Yunding@getDevices',
        'members' => 'Yunding@getMembers',
        'customer_snaps' => 'Yunding@getCustomerSnaps',
    ]
]);

// 前台

$userRoute = $route->middleware(Auth::class, 'user');

$userRoute->add([
    'POST' => [
        'order' => 'Order@add',
        'registerMerchant' => 'Auth@registerMerchant'
    ],
    'GET' => ['order' => 'Order@info']
]);

$userAuthRoute = $userRoute->prefix('user');

$userAuthRoute->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        '' => 'User@update',
        'trade_note' => 'User@updateTradeNote',
        'mobile' => 'Auth@changeMobile'
    ],
    'POST' => ['trade' => 'User@trade'],
    'GET' => [
        'trades' => 'User@getTrades',
        'addresses' => 'Address@list'
    ]
]);

$userAuthRoute->resource('address', ['add', 'update', 'del', 'show']);

// 后台

$adminRoute = Route::prefix('admin')->middleware(RecordRequest::class);

$adminRoute->add(['POST' => ['login' => 'Auth@adminLogin']]);

$adminAuthRoute = $adminRoute->middleware(Auth::class, 'admin');

$adminAuthRoute->add([
    'PUT' => [
        'password' => 'Auth@setPassword',
        'mobile' => 'Auth@changeMobile',
        'admin_name' => 'Admin@update',
        'admin_role' => 'Admin@setRole',
    ],
    'POST' => [
        'save_access' => 'Role@saveRoutes',
        'sku/io' => 'Sku@io',
    ],
    'GET' => [
        'routes' => 'Role@listRoutes',
        'sku/io_records' => 'Sku@getIoRecords',
    ],
]);

$adminAuthRoute->resource('role', ['add', 'update', 'del', 'list']);
$adminAuthRoute->resource('admin', ['add', 'del', 'list']);
$adminAuthRoute->resource('sku', ['add', 'list', 'update', 'del']);
