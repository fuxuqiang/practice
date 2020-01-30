<?php

namespace vendor;

class HttpClient
{
    private $mh, $chs;

    public function __construct($multi = false)
    {
        $multi && $this->mh = curl_multi_init();
    }

    public function request()
    {
        $active = null;
        $totalSuccesses = [];
        do {
            curl_multi_exec($this->mh, $active);
            foreach ($this->chs as $ch) {
                if (
                    curl_getinfo($ch['handle'], CURLINFO_HTTP_CODE) == 200
                    && !in_array($ch['handle'], $totalSuccesses)
                ) {
                    $totalSuccesses[] = $ch['handle'];
                    yield $ch;
                }
            }
        } while ($active);
    }

    public function addCurl($url, $params, $opt = [])
    {
        curl_multi_add_handle($this->mh, $ch = $this->getHandle($url, $params, $opt));
        $this->chs[] = ['params' => $params, 'handle' => $ch];
    }

    public function getHandle($url, $params, $opt = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $opt + [
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true
        ]);
        return $ch;
    }
}
