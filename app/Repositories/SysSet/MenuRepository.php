<?php

namespace App\Repositories\SysSet;

use App\Models\Main\SysMenu;
use App\Exceptions\XClientException;

class MenuRepository
{


    public function index()
    {
        $aryMenu = SysMenu::model()->getAllMenu();

        $menuTree = SysMenu::model()->recurseMenu($aryMenu);

        return [
            'menu' => $menuTree
        ];
    }


    public function store(array $aryDict)
    {
        $insert_id = SysMenu::model()->store($aryDict);

        return [
            'id' => $insert_id,
            'msg' => '添加成功',
        ];
    }


    public function update(array $aryDict)
    {
        if (empty($aryDict['id'])) {
            throw new XClientException('缺少ID');
        }

        $menu = SysMenu::model()->getById((int)$aryDict['id']);

        SysMenu::model()->update($menu->id, $aryDict);

        return [
            'msg' => '操作成功',
        ];
    }



    public function destroy(array $aryDict)
    {
        if (empty($aryDict['id'])) {
            throw new XClientException('缺少ID');
        }

        $menu = SysMenu::model()->getById((int)$aryDict['id']);

        SysMenu::model()->deleteById($menu->id);

        return [
            'msg' => '操作成功',
        ];
    }


    public function parent(array $aryDict)
    {
        if (empty($aryDict['id'])) {
            throw new XClientException('缺少ID');
        }

        $menu = SysMenu::model()->getById((int)$aryDict['id']);

        $aryMenu = SysMenu::model()->getAllMenu();

        // 父级的父级菜单ID
        $parent_parent_id = 0;
        foreach ($aryMenu as $arr) {
            if ($menu->parent_id == $arr['id']) {
                $parent_parent_id = $arr['parent_id'];
                break;
            }
        }

        $aryData = [
            [
                'id' => 0,
                'parent_id' => 0,
                'name' => '顶级',
                'showName' => '顶级',
            ]
        ];
        foreach ($aryMenu as $arr) {
            if ($parent_parent_id == $arr['parent_id']) {
                $aryData[] = [
                    'id' => $arr['id'],
                    'parent_id' => $arr['parent_id'],
                    'name' => $arr['name'],
                    'showName' => $arr['name'] . '[排序:' . $arr['seq_order'] . '][ID:' . $arr['id'] . ']',
                ];
            }
        }

        return [
            'parentMenu' => $aryData
        ];
    }


    public function sibling(array $aryDict)
    {
        if (empty($aryDict['id'])) {
            throw new XClientException('缺少ID');
        }

        $menu = SysMenu::model()->getById((int)$aryDict['id']);

        $aryMenu = SysMenu::model()->getAllMenu();

        $aryData = [
            [
                'id' => 0,
                'parent_id' => 0,
                'name' => '顶级',
                'showName' => '顶级',
            ]
        ];
        foreach ($aryMenu as $arr) {
            if ($menu->parent_id == $arr['parent_id']) {
                $aryData[] = [
                    'id' => $arr['id'],
                    'parent_id' => $arr['parent_id'],
                    'name' => $arr['name'],
                    'showName' => $arr['name'] . '[排序:' . $arr['seq_order'] . '][ID:' . $arr['id'] . ']',
                ];
            }
        }

        return [
            'sameMenu' => $aryData
        ];
    }



}
