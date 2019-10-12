<?php

namespace App\Models\Main;

use App\Models\Base;
use App\Exceptions\XClientException;

class SysMenu extends Base
{

    const TABLE_NAME = 'sys_menu';


    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        return $this->mainDb()->table(self::TABLE_NAME);
    }


    public function store(array $aryDict)
    {
        if (empty($aryDict['name'])) {
            throw new XClientException('菜单名称不能为空');
        }
        if (empty($aryDict['type'])) {
            throw new XClientException('参数错误');
        }

        $aryAttr = [
            'parent_id' => 0,
            'name' => '',
            'type' => 1,
            'route_api' => '',
            'route_web' => '',
            'seq_order' => 0,
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = $this->handleParamValue($value);
            }
        }

        if (!empty($aryDict['id'])) {
            $aryAttr['id'] = $aryDict['id'];
        }

        $insert_id = $this->getQuery()->insertGetId($aryAttr);

        if (empty($aryDict['seq_order'])) {
            $this->update($insert_id, ['seq_order' => $insert_id]);
        }

        return $insert_id;
    }


    public function update($menu_id, array $aryDict)
    {
        $fields = [
            'parent_id',
            'name',
            'type',
            'route_api',
            'route_web',
            'seq_order',
        ];

        $aryAttr = [];
        foreach ($aryDict as $k => $value) {
            if (in_array($k, $fields)) {
                $aryAttr[$k] = $this->handleParamValue($value);
            }
        }

        if (count($aryAttr) > 0) {
            $this->getQuery()
                ->where('id', $menu_id)
                ->update($aryAttr);
        }
    }


    public function getMenuOrNotById($menu_id)
    {
        $obj = $this->getQuery()
            ->where('id', $menu_id)
            ->first();

        if (!is_null($obj)) {
            return $obj;
        }

        return false;
    }


    public function deleteById($menu_id)
    {
        $this->getQuery()
            ->where('id', $menu_id)
            ->delete();
    }



    public function isEmptyMenu()
    {
        $count = $this->getQuery()
            ->count();

        if ($count == 0) {
            return true;
        }

        return false;
    }


    public function getAllMenu()
    {
        $aryMenu = $this->getQuery()
            ->select(['id', 'parent_id', 'type', 'name', 'seq_order', 'route_api', 'route_web'])
            ->orderBy('seq_order', 'desc')
            ->orderBy('id', 'desc')
            ->get()->toArray();

        return json_decode(json_encode($aryMenu), true);
    }


    public function getMenuByIds($menuIds)
    {
        if (count($menuIds) == 0) {
            return [];
        }

        $aryMenu = $this->getQuery()
            ->whereIn('id', $menuIds)
            ->orderBy('seq_order', 'desc')
            ->orderBy('id', 'desc')
            ->get()->toArray();

        return json_decode(json_encode($aryMenu), true);
    }


    public function recurseMenu($aryMenu, $parentId = 0)
    {
        $menuData = [];

        foreach ($aryMenu as $k => $arr) {
            if ($arr['parent_id'] == $parentId) {
                $arr['showName'] = $arr['name'] . '[排序:' . $arr['seq_order'] . '][ID:' . $arr['id'] . ']';
                unset($aryMenu[$k]);
                $arr['data'] = $this->recurseMenu($aryMenu, $arr['id']);
//                $fake = [
//                    'id' => $obj->id,
//                    'name' => $obj->name,
//                    'type' => $obj->type,
//                    'route_api' => $obj->route_api,
//                    'route_web' => $obj->route_web,
//                    'seq_order' => $obj->seq_order,
//                    'data' => $this->recurseMenu($aryMenu, $obj->id),
//                ];

                $menuData[] = $arr;
            }
        }

        return $menuData;
    }


    public function recurseMenuSelect($aryMenu, $selectMenuIds = [])
    {
        foreach ($aryMenu as $k => $arr) {
            if (in_array($arr['id'], $selectMenuIds)) {
                $arr['selected'] = true;
            } else {
                $arr['selected'] = false;
            }

            if (count($arr['data']) > 0) {
                $arr['data'] = $this->recurseMenuSelect($arr['data'], $selectMenuIds);
            }

            $aryMenu[$k] = $arr;
        }

        return $aryMenu;
    }




}
