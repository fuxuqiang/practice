<?php

namespace vendor;

class Request extends Arr
{
    private $uri, $server, $user, $exists, $perPage;

    public function __construct(array $server, array $data, callable $exists, $perPage)
    {
        $this->server = $server;
        if (!$this->data = $data) {
            if (isset($server['CONTENT_TYPE']) && $server['CONTENT_TYPE'] == 'application/json') {
                $this->data = json_decode(file_get_contents('php://input'), true);
            } else {
                parse_str(file_get_contents('php://input'), $this->data);
            }
        }
        $this->exists = $exists;
        $this->perPage = $perPage;
        $this->uri = isset($server['PATH_INFO']) ? ltrim($server['PATH_INFO'], '/') : '';
    }

    /**
     * 获取$server
     */
    public function server()
    {
        return $this->server;
    }

    /**
     * 获取请求的uri
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * 获取token
     */
    public function token()
    {
        if (
            isset($this->server['HTTP_AUTHORIZATION'])
            && strpos($this->server['HTTP_AUTHORIZATION'], 'Bearer ') === 0
        ) {
            return substr($this->server['HTTP_AUTHORIZATION'], 7);
        }
    }

    /**
     * 设置请求用户
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * 获取请求用户
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * 获取分页参数
     */
    public function pageParams()
    {
        return [$this->data['page'] ?? 1, $this->data['per_page'] ?? $this->perPage];
    }

    /**
     * 验证请求参数
     */
    public function validate(array $paramsRules)
    {
        $rules = [
            'phone' => function ($phone) {
                    return preg_match('/^1[2-9]\d{9}$/', $phone);
                },
            'exists' => $this->exists,
            'array' => 'is_array',
            'min' => function ($val, $min) {
                    return $val >= $min;
                },
            'int' => function ($val) {
                    return filter_var($val, FILTER_VALIDATE_INT) !== false;
                },
            'nq' => function ($val, $diff) {
                    return $val != $diff;
                },
            'unique' => function (...$args) {
                    return !call_user_func($this->exists, ...$args);
                }
        ];
        foreach ($paramsRules as $param => $ruleItem) {
            foreach (explode('|', $ruleItem) as $rule) {
                $rule = explode(':', $rule);
                if (! $rules[$rule[0]](
                        $this->data[$param],
                        ...(isset($rule[1]) ? explode(',', $rule[1]) : []))
                    ) {
                    throw new \Exception('无效的'.$param);
                }      
            }
        }
    }
}
