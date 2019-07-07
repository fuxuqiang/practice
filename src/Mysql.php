<?php

namespace src;

class Mysql
{
    private $table, $id;
    
    private function __construct(string $table)
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
        $this->id = $id;
        return $this;
    }

    public function update(array $data)
    {
        $sql = 'UPDATE `'.$this->table.'` SET ';
        $types = '';
        foreach ($data as $key => $value) {
            $types .= 's';
            $sql .= '`'.$key.'`=?,';
        }
        $sql = rtrim($sql, ',').' WHERE `id`='.$this->id;
        return self::query($sql, $types, $data);
    }
}
