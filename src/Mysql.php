<?php

namespace src;

class Mysql
{
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
}
