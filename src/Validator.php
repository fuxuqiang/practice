<?php
namespace src;

class Validator
{
    private $input;

    public function __construct(array $input)
    {
        $this->input = $input;
    }

    public function handle($paramsRules)
    {
        $rules = [
            'phone' => function ($phone) {
                    return preg_match('/1[2-9]\d{9}/', $phone);
                },
            'exists' => function ($val, $table, $col) {
                    return mysql($table)->exists($col, $val);
                },
            'array' => 'is_array',
            'min' => function ($val, $min) {
                    return $val >= $min;
                },
            'int' => function ($val) {
                    return filter_var($val, FILTER_VALIDATE_INT) !== false;
                },
            'nq' => function ($val, $diff) {
                    return $val != $diff;
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
