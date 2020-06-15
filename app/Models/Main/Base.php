<?php

namespace App\Models\Main;

use App\Models\Util;

class Base
{

    protected $table = 'unknown';


    public static function model()
    {
        return new static;
    }


    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        return Util::mainDb()->table($this->table);
    }


    public function getTable()
    {
        return $this->table;
    }



    // 增加一列的值
    public function addColumnNum($id, $field, $num = 1)
    {
        if ($num > 0) {
            $this->getQuery()->where('id', $id)->increment($field, $num);
        }
    }


    // 重置一列的值
    public function resetColumnNum($field, $value = 0)
    {
        $this->getQuery()->update([$field => $value]);
    }


    // 计算一列的总和数
    public function getColumnSum($field)
    {
        return $this->getQuery()->sum($field);
    }


    /*
     * 同步表字段模板
     *
     */
    public function sysTableField()
    {
        $to_table = 'article_show'; // 目标表（待同步的表）
        $from_table = 'article'; // 原始表

        $to_rel_id = 'article_id'; // 目标表关联ID

        $to_field = 'article_type'; // 目标表字段
        $from_field = 'type';  // 原始表字段

        $sql = sprintf('UPDATE `%s` SET `%s` =', $to_table, $to_field);
        $sql .= sprintf(' (SELECT `%s` FROM `%s` WHERE `id` = `%s`)', $from_field, $from_table, $to_rel_id);
        $sql .= sprintf(' WHERE EXISTS(SELECT `id` FROM `%s` WHERE `id` = `%s`)', $from_table, $to_rel_id);

        $this->mainDb()->statement($sql);
    }



    public function exist($id)
    {
        return $this->getQuery()->where('id', $id)->exists();
    }


    public function getPageList($page, $pageSize = 10, $order = 'desc')
    {
        $query = $this->getQuery();

        $total = $query->count();

        $aryData = $query->orderBy('id', $order)
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()->toArray();

        return [
            'total' => $total,
            'max_page' => ceil($total / $pageSize),
            'data' => $aryData,
        ];
    }


    public function decExecCallback($time, $end_time, \Closure $callback)
    {
        $end_date = date('Y-m-d', $end_time);

        while (true) {
            $date = date('Y-m-d', $time);

            $callback($date);

            if ($end_date == $date) {
                break;
            }

            $time -= 24 * 3600;
        }
    }


    public function incExecCallback($time, $end_time, \Closure $callback)
    {
        $end_date = date('Y-m-d', $end_time);

        while (true) {
            $date = date('Y-m-d', $time);

            $callback($date);

            if ($end_date == $date) {
                break;
            }

            $time += 24 * 3600;
        }
    }


}
