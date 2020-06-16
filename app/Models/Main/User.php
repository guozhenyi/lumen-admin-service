<?php

namespace App\Models\Main;

use App\Support\Util;
use App\Exceptions\XClientException;

class User extends Base
{

    protected $table = 'user';


    /*
     * 状态
     */
    const STATUS_ACTIVE = 1;  // 正常
    const STATUS_DISABLE = 2; // 禁用



    /**
     * 新增用户
     *
     * @param string $mobile
     * @param array $aryDict
     * @return int
     */
    public function store($mobile, $aryDict)
    {
        // 做必要验证
        if (!isset($aryDict['mobile']) || !Util::verifyMobile($mobile)) {
            throw new XClientException('手机号不正确');
        }

        $aryAttr = [
            'mobile' => '',      // 手机号
            'nickname' => '',    // 昵称
            'avatar' => '',      // 头像
            'gender' => 0,       // 性别
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = Util::handleParamValue($value);
            }
        }

        if (empty($aryAttr['nickname'])) {
            $aryAttr['nickname'] = Util::defaultNickname();
        }

        if (empty($aryAttr['avatar'])) {
            $aryAttr['avatar'] = Util::defaultAvatarUrl();
        }

        return $this->getQuery()->insertGetId($aryAttr);
    }


    /**
     * 更新用户信息
     *
     * @param $user_id
     * @param array $aryDict
     */
    public function update($user_id, array $aryDict)
    {
        $fields = [
            'nickname',
            'avatar',
            'gender',
            'status',
            'updated_at',
        ];

        $aryAttr = [];

        foreach ($aryDict as $k => $value) {
            if (in_array($k, $fields)) {
                $aryAttr[$k] = Util::handleParamValue($value);
            }
        }

        if (count($aryAttr) > 0) {
            $this->getQuery()->where('id', $user_id)->update($aryAttr);
        }
    }


    /**
     * 检查手机号是否存在
     *
     * @param string $mobile
     * @return bool
     */
    public function checkMobileExist($mobile)
    {
        return $this->getQuery()
            ->where('mobile', $mobile)
            ->exists();
    }


    /**
     * 检查用户ID是否存在
     *
     * @param int $user_id
     * @return bool
     */
    public function checkUserExistById($user_id)
    {
        return $this->getQuery()
            ->where('id', $user_id)
            ->exists();
    }


    /**
     * 根据手机号查询用户
     *
     * @param string $mobile
     * @param string $error
     * @return object
     */
    public function getUserByMobile($mobile, $error = '用户不存在')
    {
        $obj = $this->getQuery()
            ->where('mobile', $mobile)
            ->first();

        if (is_null($obj)) {
            throw new XClientException($error);
        }

        return $obj;
    }


    /**
     * 根据ID查询用户
     *
     * @param int $user_id
     * @param string $error
     * @return object
     */
    public function getUserById($user_id, $error = '用户不存在')
    {
        $obj = $this->getQuery()
            ->where('id', $user_id)
            ->first();

        if (is_null($obj)) {
            throw new XClientException($error);
        }

        return $obj;
    }


    /**
     * 根据ID查询用户
     *
     * @param $user_id
     * @return object|false
     */
    public function getUserOrNotById($user_id)
    {
        $obj = $this->getQuery()
            ->where('id', $user_id)
            ->first();

        if (!is_null($obj)) {
            return $obj;
        }

        return false;
    }


    /**
     * 根据ID查询用户列表
     *
     * @param array $user_ids
     * @return array
     */
    public function getUserListById(array $user_ids)
    {
        if (count($user_ids) <= 0) {
            return [];
        }

        $users = $this->getQuery()
            ->whereIn('id', $user_ids)
            ->get();

        $aryData = [];

        foreach ($users as $o) {
            $aryData[$o->id] = $o;
        }

        return $aryData;
    }


}
