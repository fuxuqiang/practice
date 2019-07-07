<?php
namespace auth;

class Auth implements \src\Auth
{
    private $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function handle()
    {
        return isset($_SERVER['HTTP_AUTHORIZATION'])
            && ($row = \src\Mysql::query(
                'SELECT `id` FROM `'.$this->table.'` WHERE `api_token`=? AND `token_expires`>NOW()', 's',
                [substr($_SERVER['HTTP_AUTHORIZATION'], 7)]
            )->fetch_row()) ? \src\Mysql::table($this->table)->id($row[0]) : false;
    }
}
