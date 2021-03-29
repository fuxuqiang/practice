<?php

namespace App\Model;

class Cpcn
{
    /**
     * 请求中金接口
     */
    public function request(string $code, array $data)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Request version="2.0"></Request>');

        $xml->Head->TxCode = $code;

        $config = env('cpcn');

        $xml->Head->InstitutionID = $xml->Body->InstitutionID = $config['institution_id'];

        empty($data['TxSN']) && $xml->Body->TxSN = (new \DateTime)->format('YmdHisu');

        $this->setXmlAttr($xml->Body, $data);

        $xmlStr = $xml->asXML();

        $certs = [];
        openssl_pkcs12_read(file_get_contents($config['pkcs12_file']), $certs, $config['sign_pass']);

        $signature = '';
        openssl_sign($xmlStr, $signature, $certs['pkey'], OPENSSL_ALGO_SHA1);

        $response = trim(
            (new \Fuxuqiang\Framework\HttpClient)->request(
                $config['url'],
                http_build_query(['message' => base64_encode($xmlStr), 'signature' => bin2hex($signature)]),
                [CURLOPT_SSL_VERIFYPEER => false]
            )
        );

        if (!strpos($response, ',')) {
            throw new \Exception('请求超时');
        }

        return $this->verify(...explode(',', $response));
    }

    /**
     * 验证响应报文的签名
     */
    public function verify($message, $signature)
    {
        $content = base64_decode($message);

        if (
            openssl_verify($content, pack('H' . strlen($signature), trim($signature)), file_get_contents(env('cpcn')['cer_file'])) != 1
        ) {
            throw new \Exception('验签失败');
        }

        return new \SimpleXMLElement($content);
    }

    /**
     * 根据数组数据设置xml属性
     */
    private function setXmlAttr(\SimpleXMLElement $xml, array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_array(reset($value))) {
                    foreach ($value as $item) {
                        $this->setXmlAttr($xml->addChild($key), $item);
                    }
                } else {
                    $this->setXmlAttr($xml->addChild($key), $value);
                }
            } else {
                $xml->addChild($key, $value);
            }
        }
    }
}
