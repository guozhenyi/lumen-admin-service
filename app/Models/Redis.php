<?php

namespace App\Models;

class Redis
{


    /**
     * 验证码
     *
     * @param string $device
     * @return string
     */
    public static function keyCaptcha($device)
    {
        return 'app:captcha:d_' . $device;
    }


    /**
     * 用户token映射payload
     *
     * @param string $token
     * @return string
     */
    public static function keyTokenToPayload($token)
    {
        return 'app:token:payload:t_' . $token;
    }


    /**
     * 用户ID映射设备号
     *
     * @param int $user_id
     * @return string
     */
    public static function keyUserIdToDevice($user_id)
    {
        return 'app:user:device:u_' . $user_id;
    }


    /**
     * 黑名单token存在
     *
     * @param string $token 经过hash过后的token
     * @return string
     */
    public static function keyBlacklistTokenBool($token)
    {
        return 'app:token:blackExist:t_' . $token;
    }


    /**
     * 黑名单token信息
     *
     * @param string $token
     * @return string
     */
    public static function keyBlacklistTokenData($token)
    {
        return 'app:token:blacklist:t_' . $token;
    }


    /**
     * 用户状态
     *
     * @param int $user_id
     * @return string
     */
    public static function keyUserStatus($user_id)
    {
        return 'app:user:status:u_' . $user_id;
    }


    /**
     * 角色权限映射接口集合
     *
     * @param $role_id
     * @return string
     */
    public static function keySysRoleRouteApiSet($role_id)
    {
        return 'app:role:RouteApiSet:r_' . $role_id;
    }




}
