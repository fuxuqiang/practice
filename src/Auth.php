<?php
namespace src;

interface Auth
{
    public static function handle($token);
}