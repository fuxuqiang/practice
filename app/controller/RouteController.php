<?php
namespace app\controller;

class RouteController
{
    public function index(int $page = 1, int $per_page = 5, $resource = false)
    {
        $query = mysql('route');
        $resource && $query->where([['resource', 'like', '%'.$resource.'%']]);
        return $query->paginate($page, $per_page);
    }
}
