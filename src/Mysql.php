<?php

namespace src;

class Mysql
{
    private $table, $data;
    
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public static function handler()
    {
        static $mysqli;
        $mysqli || $mysqli = new \mysqli('127.0.0.1', 'guest', 'eb', 'personal');
        return $mysqli;
    }

    public static function query(string $sql, string $types, array $bounds)
    {
        $mysqli = self::handler();
        if ($stmt = $mysqli->prepare($sql)) {
            $params = [&$types];
            foreach ($bounds as $key => $bound) {
                $params[] = &$bounds[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $params);
            if (!$stmt->execute()) {
                trigger_error($mysqli->error, E_USER_ERROR);
            }
        } else {
            trigger_error($mysqli->error, E_USER_ERROR);
        }
        $rst = $stmt->get_result() ?: true;
        $stmt->close();
        return $rst;
    }

    public static function table(string $table)
    {
        return new self($table);
    }

    public function id(int $id)
    {
        $this->data['id'] = $id;
        return $this;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __call($name, $args)
    {
        if (!in_array($name, ['update', 'insert', 'replace'])) {
            trigger_error('调用未定义的方法'.self::class.'::'.$name.'()', E_USER_ERROR);
        }
        $sql = $name.' `'.$this->table.'` SET ';
        $types = '';
        foreach ($args[0] as $key => $value) {
            $types .= 's';
            $sql .= '`'.$key.'`=?,';
        }
        $sql = rtrim($sql, ',').($name == 'update' && isset($this->data['id']) ? ' WHERE `id`='.$this->data['id'] : '');
        return self::query($sql, $types, $args[0]);
    }
}
