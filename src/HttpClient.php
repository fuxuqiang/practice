<?php
namespace src;

class HttpClient
{
    public static function request(callable $callback)
    {
        $mh = curl_multi_init();
        $rst = $callback($mh);
        $active = null;
        do {
            curl_multi_exec($mh, $active);
            usleep(10000);
        } while ($active);
        return $rst;
    }

    public static function addCurl($mh, $url, $params)
    {
        $ch = self::getHandler($url, $params);
        curl_multi_add_handle($mh, $ch);
    }

    public static function getHandler($url, $params)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_POSTFIELDS => $params, CURLOPT_RETURNTRANSFER => true]);
        return $ch;
    }
}
