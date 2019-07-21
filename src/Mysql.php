<?php

namespace src;

class Mysql
{
    public $mysqli, $table;
    
    private $where, $param;

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function query($sql, $types = '', array $vars = [])
    {
        if ($stmt = $this->mysqli->prepare($sql)) {
            if ($this->param) {
                $types .= 's';
                $vars = array_merge($vars, [$this->param]);    
            }
            $types && $stmt->bind_param($types, ...array_values($vars));
            $stmt->execute() || trigger_error($this->mysqli->error);
        } else {
            trigger_error($this->mysqli->error);
        }
        $rst = $stmt->get_result() ?: true;
        $stmt->close();
        return $rst;
    }

    public function __call($name, $args)
    {
        if (!in_array($name, ['update', 'insert', 'replace'])) {
            trigger_error('调用未定义的方法'.self::class.'::'.$name.'()');
        }
        $sql = $name.' `'.$this->table.'` SET ';
        $types = '';
        foreach ($args[0] as $key => $value) {
            $types .= 's';
            $sql .= '`'.$key.'`=?,';
        }
        $sql = rtrim($sql, ',').($name == 'update' ? $this->where : '');
        return $this->query($sql, $types, $args[0]);
    }

    public function where($column, $val)
    {
        $this->where = ' WHERE `'.$column.'`=?';
        $this->param = $val;
        return $this;
    }

    public function paginate($perPage)
    {
        $page = input()['page'] ?? 1;
        $sql = 'SELECT %s FROM `'.$this->table.'`'.$this->where;
        return [
            'data' => $this->query(
                    sprintf($sql, '*').' LIMIT '.($page - 1) * $perPage.','.$perPage
                )->fetch_all(MYSQLI_ASSOC),
            'total' => $this->query(sprintf($sql, 'COUNT(*)'))->fetch_row()[0]
        ];
    }
}
