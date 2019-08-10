<?php

namespace src;

class Mysql
{
    public $mysqli;
    
    private $table, $cols, $relation, $cond, $limit = '', $params = [];

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /**
     * 执行查询
     */
    public function query($sql, array $vars = [])
    {
        if ($stmt = $this->mysqli->prepare($sql)) {
            if ($types = str_repeat('s', count($vars) + count($this->params))) {
                $vars = array_merge($vars, $this->params);    
                $stmt->bind_param($types, ...array_values($vars));
            }
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
    public function cols(...$cols)
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
     * 添加WHERE条件
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
     * 添加 WHERE {COLUMN} IS NULL 条件
     */
    public function whereNull($col)
    {
        $this->cond[] = '`'.$col.'` IS NULL';
        return $this;
    }

    /**
     * 添加 WHERE {COLUMN} IN 条件
     */
    public function whereIn($col, array $vals)
    {
        $this->cond[] = '`'.$col.'` IN ('.rtrim(str_repeat('?,', count($vals)), ',').')';
        $this->params = array_merge($this->params, $vals);
        return $this;
    }

    /**
     * 执行本实例的查询
     */
    public function get()
    {
        return $this->query($this->getDqlSql());
    }

    /**
     * 获取查询结果集
     */
    public function all(...$cols)
    {
        $cols && $this->cols = $cols;
        $data = $this->query($this->getDqlSql().$this->limit)->fetch_all(MYSQLI_ASSOC);
        if ($this->relation && ($table = key($this->relation))
            && $foreignKeysVal = array_column($data, $table.'_id')) {
            $relationData = (new self($this->mysqli))->cols(...$this->relation[$table])
                ->from($table)->whereIn('id', $foreignKeysVal)->col(null, 'id');
            $data = array_map(function ($item) use ($table, $relationData) {
                $item[$table] = $relationData[$item[$table.'_id']];
                return $item;
            }, $data);
        }
        return $data;
    }

    /**
     * 获取查询结果的指定列
     */
    public function col($col, $idx = null)
    {
        return array_column($col ? $this->all($col) : $this->all(), $col, $idx);
    }

    /**
     * 数据是否存在
     */
    public function exists($col, $val)
    {
        return $this->where($col, $val)->query($this->getDqlSql('`'.$col.'`').' LIMIT 1')->num_rows;
    }

    /**
     * 分页查询
     */
    public function paginate($page, $perPage)
    {
        $this->limit = ' LIMIT '.($page - 1) * $perPage.','.$perPage;
        return [
            'data' => $this->all(),
            'total' => $this->query($this->getDqlSql('COUNT(*)'))->fetch_row()[0]
        ];
    }

    /**
     * 执行INSERT语句
     */
    public function insert($data)
    {
        return $this->into('INSERT', $data);
    }

    /**
     * 执行REPLACE语句
     */
    public function replace($data)
    {
        return $this->into('REPLACE', $data);
    }

    /**
     * 执行INSERT或REPLACE语句
     */
    private function into($action, $data)
    {
        if (is_array(reset($data))) {
            $cols = $this->cols;
            $val = array_map(function ($item) {
                return '('.rtrim(str_repeat('?,', count($item)), ',').')';
            }, $data);
            $binds = array_reduce($data, function ($carry, $item) {
                return array_merge($carry, $item);
            }, []);
        } else {
            $cols = array_keys($data);
            $val = $binds = $data;
        }
        return $this->query(
            $action.' `'.$this->table.'` ('.implode(',', $cols).') VALUES '.implode(',', $val),
            $binds
        );
    }

    /**
     * 执行UPDATE语句
     */
    public function update($data)
    {
        return $this->query(
            $name.' `'.$this->table.'` SET '.$this->gather(array_keys($data), '`%s`=?').$this->getWhere(),
            $data
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

    /**
     * 获取查询sql
     */
    private function getDqlSql($cols = null)
    {
        return sprintf(
            'SELECT %s FROM %s %s',
            $cols ?: ($this->cols ? $this->gather($this->cols, '`%s`') : '*'),
            '`'.$this->table.'`',
            $this->getWhere()
        );
    }
}
