<?php
namespace src;

class Request extends Arr
{
    private $user, $exists;

    public function __construct($user, callable $exists)
    {
        if (!$this->data = $_REQUEST) {
            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') {
                $this->data = json_decode(file_get_contents('php://input'), true);
            } else {
                parse_str(file_get_contents('php://input'), $this->data);
            }
        }
        $this->user = $user;
        $this->exists = $exists;
    }

    public function user()
    {
        return $this->user;
    }

    public function pageParams()
    {
        return [$this->data['page'] ?? 1, $this->data['per_page'] ?? 5];
    }

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
                    throw new \InvalidArgumentException('无效的'.$param);
                }      
            }
        }
    }
}
