<?php

namespace App\Command\Crawler;

readonly class DataAndGenerators
{
    public function __construct(public array $data, public array $generators) {}
}