<?php

namespace vendor;

class HttpClient
{
    private $mh, $chs;

    public function __construct($multi = false)
    {
        $multi && $this->mh = curl_multi_init();
    }

    /**
     * 发送请求
     */
    public function multiRequest()
    {
        $active = null;
        while ($this->chs) {
            while (curl_multi_exec($this->mh, $active) === CURLM_CALL_MULTI_PERFORM);
            while ($info = curl_multi_info_read($this->mh)) {
                curl_multi_remove_handle($this->mh, $info['handle']);
                yield $this->chs[$id = (int) $info['handle']];
                unset($this->chs[$id]);
            }    
        }
    }

    /**
     * 向curl批处理会话中添加单独的curl句柄
     */
    public function addHandle($url, $params = [], $opt = [], $method = 'GET')
    {
        curl_multi_add_handle($this->mh, $ch = $this->getHandle($url, $params, $opt, $method));
        $this->chs[(int) $ch] = ['params' => $params, 'handle' => $ch];
    }

    /**
     * 执行 cURL 会话
     */
    public function request($url, $params, $opts, $method = 'POST')
    {
        return curl_exec($this->getHandle($url, $params, $opts, $method));
    }

    /**
     * 获取curl句柄
     */
    private function getHandle($url, $params, $opts, $method)
    {
        $ch = curl_init($url);
        $method == 'POST' && $opts += [CURLOPT_POSTFIELDS => $params];
        curl_setopt_array($ch, $opts + [CURLOPT_RETURNTRANSFER => true]);
        return $ch;
    }
}
