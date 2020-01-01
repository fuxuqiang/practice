<?php

namespace vendor;

class Mysql
{
    /**
     * @var \mysqli
     */
    private $mysqli;

    /**
     * @var \mysqli_stmt
     */
    private $stmt;

    /**
     * @var int
     */
    private static $trans = 0;

    /**
     * @var string
     */
    private $table, $limit, $lock, $order;

    /**
     * @var array
     */
    private $cols, $relation, $cond, $params = [];

    /**
     * @param \mysqli
     */
    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /**
     * 执行查询
     * @return \mysqli_result|bool
     */
    public function query(string $sql, array $vars = [])
    {
        if ($this->stmt = $this->mysqli->prepare($sql)) {
            if ($types = str_repeat('s', count($vars) + count($this->params))) {
                $vars = array_merge($vars, $this->params);
                $this->stmt->bind_param($types, ...array_values($vars));
            }
            $this->stmt->execute() || trigger_error($this->mysqli->error, E_USER_ERROR);
        } else {
            trigger_error($this->mysqli->error, E_USER_ERROR);
        }
        $rst = $this->stmt->get_result() ?: true;
        return $rst;
    }

    /**
     * 设置表名
     */
    public function table(string $table)
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
    public function where($col, $operator = null, $val = null)
    {
        if (is_array($col)) {
            foreach ($col as $key => $item) {
                if (is_array($item)) {
                    $this->setWhere($item[0], $item[1], $item[2]);
                } else {
                    $this->setWhere($key, '=', $item);
                }
            }
        } else {
            if (is_null($val)) {
                $val = $operator;
                $operator = '=';
            }
            $this->setWhere($col, $operator, $val);
        }
        return $this;
    }

    /**
     * 设置WHERE条件
     */
    private function setWhere($col, $operator, $val)
    {
        $this->cond[] = '`' . $col . '`' . $operator . '?';
        $this->params[] = $val;
    }

    /**
     * 添加 WHERE {COLUMN} IS NULL 条件
     */
    public function whereNull($col)
    {
        $this->cond[] = '`' . $col . '` IS NULL';
        return $this;
    }

    /**
     * 添加 WHERE {COLUMN} IN 条件
     */
    public function whereIn($col, array $vals)
    {
        $this->cond[] = '`' . $col . '` IN ' . $this->markers($vals);
        $this->params = array_merge($this->params, $vals);
        return $this;
    }

    /**
     * 添加 FOR UPDATE 锁
     */
    public function lock()
    {
        $this->lock = ' FOR UPDATE';
        return $this;
    }

    /**
     * ORDER BY RAND()
     */
    public function rand($limit)
    {
        $this->order = ' ORDER BY RAND()';
        $this->limit = 'LIMIT ' . $limit;
        return $this;
    }

    /**
     * 返回查询结果首行对象
     */
    public function get($class = null, $params = [])
    {
        $this->limit = 'LIMIT 1';
        $stmt = $this->query($this->getDqlSql());
        return $class ? $stmt->fetch_object($class, $params) : $stmt->fetch_object();
    }

    /**
     * 获取查询结果首行单个列的值
     */
    public function val($col)
    {
        $this->limit = 'LIMIT 1';
        return ($row = $this->cols($col)->get()) ? $row->$col : null;
    }

    /**
     * 获取查询结果集
     */
    public function all(...$cols)
    {
        $this->cols || $this->cols = $cols;
        $data = $this->query($this->getDqlSql())->fetch_all(MYSQLI_ASSOC);
        if (
            $this->relation && ($table = key($this->relation))
            && $foreignKeysVal = array_column($data, $table . '_id')
        ) {
            $relationData = (new self($this->mysqli))->cols(...$this->relation[$table])
                ->table($table)->whereIn('id', $foreignKeysVal)->col(null, 'id');
            $data = array_map(function ($item) use ($table, $relationData) {
                $item[$table] = $relationData[$item[$table . '_id']];
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
        $col && $this->cols = $idx ? [$col, $idx] : [$col];
        return array_column($this->all(), $col, $idx);
    }

    /**
     * 数据是否存在
     */
    public function exists($col, $val)
    {
        $this->limit = 'LIMIT 1';
        return $this->where($col, $val)->query($this->getDqlSql('`' . $col . '`'))->num_rows;
    }

    /**
     * 分页查询
     */
    public function paginate(int $page, int $perPage)
    {
        $this->limit = 'LIMIT ' . ($page - 1) * $perPage . ',' . $perPage;
        return [
            'data' => $this->all(),
            'total' => $this->query($this->getDqlSql('COUNT(*)'))->fetch_row()[0]
        ];
    }

    /**
     * 执行INSERT语句
     */
    public function insert(array $data)
    {
        $this->into('INSERT', $data);
        return $this->stmt->insert_id;
    }

    /**
     * 执行REPLACE语句
     */
    public function replace(array $data)
    {
        return $this->into('REPLACE', $data);
    }

    /**
     * 执行INSERT或REPLACE语句
     */
    private function into($action, array $data)
    {
        if (is_array(reset($data))) {
            $cols = $this->cols;
            $markers = implode(',', array_map(function ($item) {
                return $this->markers($item);
            }, $data));
            $binds = array_merge(...$data);
        } else {
            $cols = array_keys($data);
            $markers = $this->markers($data);
            $binds = $data;
        }
        return $this->query(
            $action . ' `' . $this->table . '` (' . $this->gather($cols, '`%s`') . ') VALUES ' . $markers,
            $binds
        );
    }

    /**
     * 执行UPDATE语句
     */
    public function update(array $data)
    {
        return $this->query(
            "UPDATE `$this->table` SET " . $this->gather(array_keys($data), '`%s`=?') . $this->getWhere(),
            $data
        );
    }

    /**
     * 执行DELETE语句
     */
    public function del(int $id = null)
    {
        return $id ? $this->query("DELETE FROM `$this->table` WHERE `id`=?", [$id])
            : $this->query("DELETE FROM `$this->table`" . $this->getWhere());
    }

    /**
     * 开始事务
     */
    public function begin()
    {
        self::$trans++ || $this->mysqli->begin_transaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        self::$trans-- == 1 && $this->mysqli->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback()
    {
        self::$trans-- == 1 || $this->mysqli->rollback();
    }

    /**
     * 获取WHERE子句
     */
    private function getWhere()
    {
        return $this->cond ? ' WHERE ' . implode(' AND ', $this->cond) : '';
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
        return 'SELECT ' . ($cols ?: ($this->cols ? $this->gather($this->cols, '`%s`') : '*'))
            . " FROM `$this->table`" . $this->getWhere() . $this->order . ' ' . $this->limit . $this->lock;
    }

    /**
     * 获取参数数组的绑定标记
     */
    private function markers(array $data)
    {
        return '(' . rtrim(str_repeat('?,', count($data)), ',') . ')';
    }
}
