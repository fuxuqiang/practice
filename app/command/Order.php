<?php

namespace app\command;

use src\Mysql;
use vendor\HttpClient;

class Order
{
    public function handle()
    {
        $users = Mysql::select(
            'SELECT `u`.`phone`,`a`.`id` FROM `user` `u`
            JOIN `address` `a` ON `u`.`id`=`a`.`user_id` ORDER BY RAND() LIMIT 5'
        );
        $skus = Mysql::table('sku')->col('id');
        $http = new HttpClient;
        foreach (\app\model\Login::getToken($users) as $user) {
            $http->request(
                'http://practice.test/order',
                json_encode([
                    'address_id' => $user['id'],
                    'skus' => array_map(function ($val) use ($skus) {
                        return ['id' => $skus[$val], 'num' => mt_rand(1, 3)];
                    }, array_rand($skus, mt_rand(1, count($skus))))
                ]),
                [
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $user['token'],
                        'Content-Type: application/json'
                    ]
                ]
            );
        }
    }
}
