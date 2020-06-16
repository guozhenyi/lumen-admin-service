<?php

namespace App\Models\Main;

use App\Models\Redis;
use App\Models\Util;
use App\Exceptions\XClientException;

class SysRole extends Base
{

    protected $table = 'sys_role';


    // 系统管理员
    const SUPER_ADMIN = -1;


    public function store(array $aryDict)
    {
        if (empty($aryDict['name'])) {
            throw new XClientException('参数错误');
        }

        $aryAttr = [
            'name' => '',
            'describe' => '',
            'menu_ids' => '[]',
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = Util::handleParamValue($value);
            }
        }

        return $this->getQuery()->insertGetId($aryAttr);
    }


    public function update($role_id, array $aryDict)
    {
        $fields = [
            'name',
            'describe',
            'menu_ids',
        ];

        $aryAttr = [];
        foreach ($aryDict as $k => $value) {
            if (in_array($k, $fields)) {
                $aryAttr[$k] = Util::handleParamValue($value);
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
     * @date 2019-03-20
     */
    public function getRoleRouteApi($role_id, $timeout = 3600)
    {
        $redis = Util::redis();
        $key = Redis::keySysRoleRouteApiSet($role_id);

        if ($redis->exists($key)) {
            return $redis->smembers($key);
        }

        $sysRole = SysRole::model()->getRoleOrNotById($role_id);

        if ($sysRole === false) {
            $redis->sadd($key, ['false']);
            $redis->expire($key, $timeout);
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
            Util::redis()->sadd($key, $routes);
        } else {
            Util::redis()->sadd($key, ['false']);
        }

        Util::redis()->expire($key, $timeout);

        return $routes;
    }

    /**
     * 销毁缓存
     *
     * @param $role_id
     * @date 2019-03-20
     */
    public function delRoleRouteApi($role_id)
    {
        $key = Redis::keySysRoleRouteApiSet($role_id);
        Util::redis()->del([$key]);
    }


}
