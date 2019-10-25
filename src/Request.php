<?php
namespace src;

class Request extends Arr
{
    private $user, $exists, $perPage;

    public function __construct(array $data, $user, callable $exists, int $perPage)
    {
        $this->data = $data;
        $this->user = $user;
        $this->exists = $exists;
        $this->perPage = $perPage;
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
                    return preg_match('/1[2-9]\d{9}/', $phone);
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
