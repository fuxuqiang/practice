<?php

namespace src;

class JWT
{
    private $header, $jti, $exp;

    public function __construct($jti, $exp)
    {
        $this->header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $this->jti = $jti;
        $this->exp = $exp;
    }

    public function encode($sub)
    {
        return $this->base64Encode($this->header) . '.'
            . $this->base64Encode(['sub' => $sub, 'exp' => time() + $this->exp, 'jti' => $this->jti]) . '.';
    }

    public function decode($token)
    {
        $data = explode('.', $token);
        if (count($data) != 3) {
            throw new \Exception('token格式错误');
        }
        $header = $this->base64Decode($data[0]);
        if ($header->alg != $this->header['alg']) {
            return false;
        }
        $payload = $this->base64Decode($data[1]);
        if ($payload->exp < time() || $payload->jti != $this->jti) {
            return false;
        }
        return $payload->sub;
    }

    private function base64Encode($data)
    {
        return str_replace('=', '', strtr(base64_encode(json_encode($data)), '+/', '-_'));
    }

    private function base64Decode($data)
    {
        ($remainder = strlen($data) % 4) && $data .= str_repeat('=', 4 - $remainder);
        return json_decode(base64_decode(strtr($data, '-_', '+/')));
    }
}
