<?php
namespace app\command;

use src\Http;

class Order
{
    public function handle()
    {
        $users = mysql()->query(
            'SELECT `u`.`phone`,`a`.`id` FROM `user` `u`
            JOIN `address` `a` ON `u`.`id`=`a`.`user_id` ORDER BY RAND() LIMIT 5'
        )->fetch_all(MYSQLI_ASSOC);

        $users = \app\model\Login::getToken($users);

        Http::request(function ($mh) use ($users) {
            $skus = mysql('sku')->col('id');
            foreach ($users as $user) {
                $ch = Http::getHandler('http://stock.test/order', json_encode([
                    'address_id' => $user['id'],
                    'skus' => array_map(function ($val) {
                        return ['id' => $val, 'num' => mt_rand(1, 3)];
                    }, array_rand($skus, mt_rand(2, count($skus))))
                ]), true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer '.$user['token'],
                    'Content-Type: application/json'
                ]);
                curl_multi_add_handle($mh, $ch);
            }
        });
    }
}
