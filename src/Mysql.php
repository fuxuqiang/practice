<?php

namespace src;

class Mysql
{
    public $mysqli;
    
    private $table, $params, $cols, $relation, $cond;

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /**
     * 执行查询
     */
    public function query($sql, $types = '', array $vars = [])
    {
        if ($stmt = $this->mysqli->prepare($sql)) {
            if ($this->params) {
                $types .= str_repeat('s', count($this->params));
                $vars = array_merge($vars, $this->params);
            }
            $types && $stmt->bind_param($types, ...array_values($vars));
            $stmt->execute() || trigger_error($this->mysqli->error, E_USER_ERROR);
        } else {
            trigger_error($this->mysqli->error, E_USER_ERROR);
        }
        $rst = $stmt->get_result() ?: true;
        $stmt->close();
        return $rst;
    }

    /**
     * 设置表名
     */
    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 设置查询列
     */
    public function select(...$cols)
    {
        $this->cols = $cols;
        return $this;
    }

    /**
     * 设置关联查询
     */
    public function with(array $relation)
    {
        $this->relation = $relation;
        return $this;
    }

    /**
     * 设置WHERE条件
     */
    public function where($col, $val = null)
    {
        if (is_array($col)) {
            foreach ($col as $item) {
                $this->cond[] = '`'.$item[0].'` '.$item[1].' ?';
                $this->params[] = $item[2];
            }
        } else {
            $this->cond[] = '`'.$col.'`=?';
            $this->params[] = $val;
        }
        return $this;
    }

    /**
     * 设置 WHERE {COLOMN} IS NULL 条件
     */
    public function whereNull($col)
    {
        $this->cond[] = '`'.$col.'` IS NULL';
        return $this;
    }

    /**
     * 分页查询
     */
    public function paginate($page, $perPage)
    {
        $sql = 'SELECT %s FROM `'.$this->table.'`'.$this->getWhere();
        $data = $this->query(
            sprintf($sql, $this->cols ? $this->gather($this->cols, '`%s`') : '*')
            .' LIMIT '.($page - 1) * $perPage.','.$perPage
        )->fetch_all(MYSQLI_ASSOC);
        if ($this->relation && ($table = key($this->relation))
            && $foreignKeysVal = array_column($data, $table.'_id')) {
            $relationData = array_column(
                $this->query(
                    'SELECT '.$this->gather($this->relation[$table], '`%s`').' FROM `'.$table.'`
                    WHERE `id` IN ('.implode(',', $foreignKeysVal).')'
                )->fetch_all(MYSQLI_ASSOC),
                null,
                'id'
            );
            $data = array_map(function ($item) use ($table, $relationData) {
                $item[$table] = $relationData[$item[$table.'_id']];
                return $item;
            }, $data);
        }
        return [
            'data' => $data,
            'total' => $this->query(sprintf($sql, 'COUNT(*)'))->fetch_row()[0]
        ];
    }

    /**
     * update,insert,replace方法
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['update', 'insert', 'replace'])) {
            trigger_error('调用未定义的方法'.self::class.'::'.$name.'()', E_USER_ERROR);
        }
        return $this->query(
            $name.' `'.$this->table.'` SET '.$this->gather(array_keys($args[0]), '`%s`=?')
            .($name == 'update' ? $this->getWhere() : ''),
            str_repeat('s', count($args[0])),
            $args[0]
        );
    }

    /**
     * 获取WHERE子句
     */
    private function getWhere()
    {
        return $this->cond ? ' WHERE '.implode(' AND ', $this->cond) : '';
    }

    /**
     * 格式化数组元素后用,连接成字符串
     */
    private function gather(array $arr, $format)
    {
        return implode(',', array_map(function ($val) use ($format) {
            return sprintf($format, $val);
        }, $arr));
    }
}
