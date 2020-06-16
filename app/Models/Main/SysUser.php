<?php

namespace App\Models\Main;

use App\Models\Util;
use App\Exceptions\XClientException;

class SysUser extends Base
{

    protected $table = 'sys_user';


    /*
     * 状态
     */
    const STATUS_ACTIVE = 1;  // 正常
    const STATUS_DISABLE = 2; // 停用


    /**
     * @param array $aryDict
     * @return int
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-12
     */
    public function store(array $aryDict)
    {
        $aryAttr = [
            'username' => '',
            'password' => '',
            'role_id' => 0,
            'name' => '',
            'avatar' => '',
            'mobile' => '',
            'gender' => 0,
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = Util::handleParamValue($value);
            }
        }

        return $this->getQuery()->insertGetId($aryAttr);
    }


    /**
     * @param $user_id
     * @param array $aryDict
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-02-25
     */
    public function update($user_id, array $aryDict)
    {
        $fields = [
            'password',
            'name',
            'avatar',
            'mobile',
            'gender',
        ];

        $aryAttr = [];

        foreach ($aryDict as $k => $val) {
            if (in_array($k, $fields)) {
                $aryAttr[$k] = $val;
            }
        }

        if (!isset($aryAttr['updated_at'])) {
            $aryAttr['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->getQuery()
            ->where('id', $user_id)
            ->update($aryAttr);
    }


    public function getByUsername($username)
    {
        $obj = $this->getQuery()
            ->where('username', $username)
            ->first();

        if (is_null($obj)) {
            throw new XClientException('用户不存在');
        }

        return $obj;
    }


    public function getByUserId($user_id)
    {
        $user = $this->getQuery()
            ->where('id', $user_id)
            ->first();

        if (is_null($user)) {
            throw new XClientException('用户不存在');
        }

        return $user;
    }


    public function getOrNotByUserId($user_id)
    {
        $user = $this->getQuery()
            ->where('id', $user_id)
            ->first();

        if (!is_null($user)) {
            return $user;
        }

        return false;
    }


}
