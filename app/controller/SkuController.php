<?php
namespace app\controller;

use src\Mysql;
use vendor\Request;

class SkuController
{
    /**
     * 添加
     */
    public function add(Request $request)
    {
        Mysql::table('sku')->insert(
            $request->validate(['price' => 'int|min:0|required', 'name' => 'str|required'])
        );
        return msg('添加成功');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'price' => 'int|min:0', 'name' => 'str', 'id' => 'required|int'
        ]);
        $id = $data['id'];
        unset($data['id']);
        if (!$data) {
            return error('参数错误');
        }
        Mysql::table('sku')->where('id', $id)->update($data);
        return msg('修改成功');
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request->validate(['keyword' => 'str']);
        $query = Mysql::table('sku');
        ($name = $request->name) && $query->where('name', 'LIKE', '%' . $name . '%');
        return $query->paginate(...$request->pageParams());
    }

    /**
     * 删除
     */
    public function del($id)
    {
        Mysql::table('sku')->where('id', $id)->update(['deleted_at' => timestamp()]);
        return msg('删除成功');
    }

    /**
     * 库存变更
     */
    public function io(Request $request)
    {
        $data = $request->validate([
            'num' => 'int|required', 'id' => 'required|exists:sku,id', 'note' => 'str'
        ]);
        if (Mysql::table('sku')->where('id', $data['id'])->val('stock') + $data['num'] < 0) {
            return error('库存不足');
        }
        Mysql::begin();
        try {
            $sku = \app\model\Sku::find($data['id']);
            $sku->io(['admin_id' => $request->userId(), 'num' => $data['num']]);
            Mysql::commit();
            return msg('更新成功');
        } catch (\Exception $e) {
            Mysql::rollback();
            return error('更新失败');
        }
    }

    /**
     * 查询库存变更记录
     */
    public function getIoRecords(Request $request)
    {
        $params = $request->validate([
            'keyword' => 'str', 'date_from' => 'str', 'date_to' => 'str'
        ]);
        $query = Mysql::selectRaw('`s`.`name`,`r`.`num`,`a`.`name` AS `admin_name`,`r`.`note`')
            ->from('`sku_record` `r` JOIN `sku` `s` ON `s`.`id`=`r`.`sku_id` JOIN `admin` `a` ON `a`.`id`=`r`.`admin_id`');
        if ($params) {
            if (!empty($params['keyword'])) {
                $keyword = '%' . $params['keyword'] . '%';
                $query->whereRaw('(`a`.`name` LIKE ? OR `s`.`name` LIKE ?)', [$keyword, $keyword]);
            }
            empty($params['date_from']) || $query->where('created_at', '>=', $params['date_from']);
            if (!empty($params['date_to'])) {
                $query->whereRaw('DATE(`created_at`)<=?', [date('Y-m-d', strtotime($params['date_to']))]);
            }
        }
        return $query->paginate(...$request->pageParams());
    }
}
