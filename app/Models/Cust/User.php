<?php

namespace App\Models\Cust;

use App\Models\Base;
use App\Exceptions\XClientException;

class User extends Base
{

    const TABLE_NAME = 'user';


    /*
     * 状态
     */
    const STATUS_ACTIVE = 1;  // 正常
    const STATUS_DISABLE = 2; // 禁用


    /*
     * 默认测试手机号
     */
    const TEST_MOBILE = '13295801086'; // iOS
    const TEST_MOBILE_TWO = '13295801087'; // Android
    const TEST_SMS_CODE = '1234';


    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        return $this->mainDb()->table(self::TABLE_NAME);
    }


    /**
     * 用户默认头像
     *
     * @return string
     */
    public function avatarUrl()
    {
        $avatar = env('AVATAR_URL');

        if (empty($avatar)) {
            $avatar = 'https://yourdomain.com/avatar.png';
        }

        return $avatar;
    }


    /**
     * 是否默认头像
     *
     * @param $avatar
     * @return bool
     */
    public function verifyAvatar($avatar)
    {
        if ($avatar == $this->avatarUrl()) {
            return true;
        }

        return false;
    }


    /**
     * 默认昵称
     *
     * @return string
     */
    public function nickname()
    {
        return 'app' . $this->randNum(mt_rand(5, 8));
    }


    /**
     * 是否默认昵称
     *
     * @param $nickname
     * @return bool
     */
    public function verifyNickname($nickname)
    {
        if (preg_match('/^app\d*$/', $nickname)) {
            return true;
        }

        return false;
    }


    /**
     * 新增用户
     *
     * @param string $mobile
     * @param array $aryDict
     * @return int
     */
    public function store($mobile, $aryDict)
    {
        if (!$this->verifyMobile($mobile)) {
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
                $aryAttr[$k] = $this->handleParamValue($value);
            }
        }

        $aryAttr['mobile'] = $mobile;

        if (empty($aryAttr['nickname'])) {
            $aryAttr['nickname'] = $this->nickname();
        }

        if (empty($aryAttr['avatar'])) {
            $aryAttr['avatar'] = $this->avatarUrl();
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
                $aryAttr[$k] = $this->handleParamValue($value);
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
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-09
     */
    public function checkMobileExist($mobile)
    {
        return $this->getQuery()
            ->where('mobile', $mobile)
            ->exists();
    }


    /**
     * 检查用户是否存在
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
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-27
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
