<?php

namespace src;

class Mysql
{
    public $mysqli;
    
    private $table, $param, $cols, $conds = [];

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
     * 设置 WHERE {COLUMN}={VALUE} 条件
     */
    public function where($col, $val)
    {
        $this->conds[] = '`'.$col.'`=?';
        $this->param = $val;
        return $this;
    }

    /**
     * 设置 WHERE {COLOMN} IS NULL条件
     */
    public function whereNull($col)
    {
        $this->conds[] = '`'.$col.'` IS NULL';
        return $this;
    }

    /**
     * 分页查询
     */
    public function paginate($page, $perPage)
    {
        $sql = 'SELECT %s FROM `'.$this->table.'`'.$this->getWhere();
        return [
            'data' => $this->query(
                    sprintf($sql, $this->cols ? implode(',', array_map(function ($val) {
                        return '`'.$val.'`';
                    }, $this->cols)) : '*').' LIMIT '.($page - 1) * $perPage.','.$perPage
                )->fetch_all(MYSQLI_ASSOC),
            'total' => $this->query(sprintf($sql, 'COUNT(*)'))->fetch_row()[0]
        ];
    }

    /**
     * update,insert,replace方法
     */
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
        $sql = rtrim($sql, ',').($name == 'update' ? $this->getWhere() : '');
        return $this->query($sql, $types, $args[0]);
    }

    /**
     * 获取WHERE子句
     */
    private function getWhere()
    {
        return $this->conds ? ' WHERE '.implode(' AND ', $this->conds) : '';
    }
}
