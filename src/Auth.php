<?php
namespace src;

interface Auth
{
    public function __construct($table);

    public function handle();
}