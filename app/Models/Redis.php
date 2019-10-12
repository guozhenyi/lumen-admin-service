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
        return 'dpAdmin:captcha:d_' . $device;
    }


    /**
     * 用户token映射payload
     *
     * @param string $token
     * @return string
     */
    public function keyTokenToPayload($token)
    {
        return 'dpAdmin:token:payload:t_' . $token;
    }


    /**
     * 用户ID映射设备号
     *
     * @param int $user_id
     * @return string
     */
    public function keyUserIdToDevice($user_id)
    {
        return 'dpAdmin:user:device:u_' . $user_id;
    }


    /**
     * 黑名单token存在
     *
     * @param string $token 经过hash过后的token
     * @return string
     */
    public function keyBlacklistTokenBool($token)
    {
        return 'dpAdmin:token:blackExist:t_' . $token;
    }


    /**
     * 黑名单token信息
     *
     * @param string $token
     * @return string
     */
    public function keyBlacklistTokenData($token)
    {
        return 'dpAdmin:token:blacklist:t_' . $token;
    }

    /**
     * 用户状态
     *
     * @param int $user_id
     * @return string
     */
    public function keyUserStatus($user_id)
    {
        return 'dpAdmin:user:status:u_' . $user_id;
    }


}
