<?php

namespace App\Models\Main;

use App\Models\Base;
use App\Models\Redis;
use App\Exceptions\XClientException;

class SysRole extends Base
{

    const TABLE_NAME = 'sys_role';

    // 系统管理员
    const SUPER_ADMIN = -1;

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
            throw new XClientException('参数错误');
        }

        $aryAttr = [
            'name' => '',
            'desc' => '',
            'menu_ids' => '[]',
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = $this->handleParamValue($value);
            }
        }

        return $this->getQuery()->insertGetId($aryAttr);
    }


    public function update($role_id, array $aryDict)
    {
        $fields = [
            'name',
            'desc',
            'menu_ids',
        ];

        $aryAttr = [];
        foreach ($aryDict as $k => $value) {
            if (in_array($k, $fields)) {
                $aryAttr[$k] = $this->handleParamValue($value);
            }
        }

        if (count($aryAttr) > 0) {
            $this->getQuery()->where('id', $role_id)
                ->update($aryAttr);
        }

        if (isset($aryAttr['menu_ids'])) {
            $this->delRoleRouteApi($role_id);
        }
    }


    public function checkNameExist($name)
    {
        $obj = $this->getQuery()
            ->where('name', $name)
            ->first();

        if (!is_null($obj)) {
            return true;
        }

        return false;
    }


    public function deleteById($role_id)
    {
        $this->getQuery()
            ->where('id', $role_id)
            ->delete();
    }


    public function getRoleOrNotById($role_id)
    {
        $obj = $this->getQuery()
            ->where('id', $role_id)
            ->first();

        if (!is_null($obj)) {
            return $obj;
        }

        return false;
    }


    public function getPageRoleList($page, $pageSize = 10)
    {
        $query = $this->getQuery();

        $total = $query->count();

        $aryData = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()->toArray();

        return [
            'total' => $total,
            'max_page' => ceil($total / $pageSize),
            'data' => $aryData,
        ];
    }



    public function getRoleListByIds(array $roleIds)
    {
        if (count($roleIds) == 0) {
            return [];
        }

        $aryRole = $this->getQuery()
            ->whereIn('id', $roleIds)
            ->get()->toArray();

        $data = [];
        foreach ($aryRole as $obj) {
            $data[$obj->id] = json_decode(json_encode($obj), true);
        }

        return $data;
    }


    public function getRoleList()
    {
        return $this->getQuery()
            ->orderBy('id', 'desc')
            ->get()->toArray();
    }

    /**
     * 获得角色能访问的接口列表
     *
     * @param $role_id
     * @param int $timeout
     * @return array
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-20
     */
    public function getRoleRouteApi($role_id, $timeout = 3600)
    {
        $key = Redis::model()->keySysRoleRouteApiSet($role_id);

        if ($this->redis()->exists($key)) {
            return $this->redis()->smembers($key);
        }

        $sysRole = SysRole::model()->getRoleOrNotById($role_id);

        if ($sysRole === false) {
            $this->redis()->sadd($key, ['false']);
            $this->redis()->expire($key, $timeout);
            return [];
        }

        $menu_ids = array_values((array)json_decode($sysRole->menu_ids));

        $aryMenu = SysMenu::model()->getMenuByIds($menu_ids);

        $routes = [];
        foreach ($aryMenu as $arr) {
            if (!empty($arr['route_api'])) {
                $routes[] = $arr['route_api'];
            }
        }

        if (count($routes) > 0) {
            $this->redis()->sadd($key, $routes);
        } else {
            $this->redis()->sadd($key, ['false']);
        }

        $this->redis()->expire($key, $timeout);

        return $routes;
    }

    /**
     * 销毁缓存
     *
     * @param $role_id
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-20
     */
    public function delRoleRouteApi($role_id)
    {
        $key = Redis::model()->keySysRoleRouteApiSet($role_id);
        $this->redis()->del([$key]);
    }


}
