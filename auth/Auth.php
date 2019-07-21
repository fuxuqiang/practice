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
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $field = '`id`'.($this->table == 'admin' ? ',`role_id`' : '');
            $rst = mysql()->query(
                'SELECT '.$field.' FROM `'.$this->table.'` WHERE `api_token`=? AND `token_expires`>NOW()',
                's',
                [substr($_SERVER['HTTP_AUTHORIZATION'], 7)]
            );
            if ($rst->num_rows) {
                return $rst->fetch_object(
                    ...$this->table == 'admin' ? [\model\Admin::class] : [\src\Model::class, 'user']
                );
            }
        }
        return false;
    }
}
