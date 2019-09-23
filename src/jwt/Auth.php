<?php
namespace src\jwt;

interface Auth
{
    public static function handle(string $token, JWT $jwt);
}