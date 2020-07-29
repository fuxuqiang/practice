<?php

function env($name)
{
    static $env;
    $env || $env = parse_ini_file(__DIR__ . '/../.env', true);
    return $env[$name] ?? null;
}
