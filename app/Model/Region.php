<?php

namespace App\Model;

use Fuxuqiang\Framework\Model\{ModelQuery, Model};

/**
 * @method static ModelQuery search(string $name)
 */
class Region extends Model
{
    const CODE = 'code', NAME = 'name', EN_NAME = 'en_name', SHORT_EN_NAME = 'short_en_name';

    protected string $primaryKey = self::CODE;

    public int $code;

    public string $name;

    /**
     * 获取所有级联区域代码
     */
    public function getAllCode(): array
    {
        return [
            (int) substr($this->code, 0, 2),
            self::getParentCode(4),
            self::getParentCode(6),
            self::getParentCode(9),
            $this->code > 99999999999 ? $this->code : 0,
        ];
    }

    /**
     * 获取上级区域代码
     */
    private function getParentCode($len): int
    {
        return strlen($this->code) >= $len ? (int) substr($this->code, 0, $len) : 0;
    }

    /**
     * 根据名称搜索区域
     */
    public function scopeSearch(ModelQuery $query, string $name): ModelQuery
    {
        return $query->whereLike(self::NAME, $name.'%');
    }

    /**
     * 获取下级区域
     */
    public function scopeChild(ModelQuery $query, int $code): ModelQuery
    {
        $factor = $code > 99999 ? 1000 : (in_array($code, [4419, 4420]) ? 100000 : 100);
        return $query->whereBetween(self::CODE, [$code * $factor, ($code + 1) * $factor]);
    }
}
