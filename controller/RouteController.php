<?php
namespace controller;

class RouteController
{
    public function index(int $page = 1, int $per_page = 5)
    {
        return [
            'data' => mysql('route')->paginate($page, $per_page)
        ];
    }
}
