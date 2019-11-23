<?php

namespace vendor;

class Arr
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(...$keys)
    {
        return $keys ? array_intersect_key($this->data, array_flip($keys)) : $this->data;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }
}
