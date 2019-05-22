<?php

namespace App\Models;

class Redis
{

    public static function model()
    {
        return new static;
    }


    /**
     * 验证码
     *
     * @param string $device
     * @return string
     */
    public function keyCaptcha($device)
    {
        return 'app:captcha:d_' . $device;
    }


    /**
     * 用户token映射payload
     *
     * @param string $token
     * @return string
     */
    public function keyTokenToPayload($token)
    {
        return 'app:token:payload:t_' . $token;
    }


    /**
     * 用户ID映射设备号
     *
     * @param int $user_id
     * @return string
     */
    public function keyUserIdToDevice($user_id)
    {
        return 'app:user:device:u_' . $user_id;
    }


    /**
     * 黑名单token存在
     *
     * @param string $token 经过hash过后的token
     * @return string
     */
    public function keyBlacklistTokenBool($token)
    {
        return 'app:token:blackExist:t_' . $token;
    }


    /**
     * 黑名单token信息
     *
     * @param string $token
     * @return string
     */
    public function keyBlacklistTokenData($token)
    {
        return 'app:token:blacklist:t_' . $token;
    }

    /**
     * 用户状态
     *
     * @param int $user_id
     * @return string
     */
    public function keyUserStatus($user_id)
    {
        return 'app:user:status:u_' . $user_id;
    }


}
