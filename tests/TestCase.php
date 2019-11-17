<?php

namespace tests;

class TestCase extends \src\TestCase
{
    protected function getCode($phone)
    {
        $this->post('sendCode', ['phone' => $phone]);
        return $_SESSION['code_' . $phone];
    }
}
