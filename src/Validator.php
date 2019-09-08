<?php
namespace src;

class Validator
{
    private $input, $exists;

    public function __construct(array $input, callable $exists)
    {
        $this->input = $input;
        $this->exists = $exists;
    }

    public function handle($paramsRules)
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
                        $this->input[$param],
                        ...(isset($rule[1]) ? explode(',', $rule[1]) : []))
                    ) {
                    return $param;
                }      
            }
        }
        return false;
    }
}
