<?php

function config($name)
{
    static $config;
    $config || $config = parse_ini_file(__DIR__ . '/../.env', true);
    return $config[$name] ?? null;
}