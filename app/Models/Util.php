<?php

namespace App\Models;


class Util
{

    /**
     * 默认MySQL连接
     *
     * @return \Illuminate\Database\DatabaseManager
     */
    public static function mainDb()
    {
        return app('db');
    }


    /**
     * Redis连接
     *
     * @return \Illuminate\Redis\RedisManager
     */
    public static function redis()
    {
        return app('redis');
    }


    /**
     * 日志
     *
     * @return \Monolog\Logger
     */
    public static function logger()
    {
        return app('log');
    }


    /**
     * 处理参数值
     *
     * @param $value
     * @return string
     */
    public static function handleParamValue($value)
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }


    /**
     * 随机数
     *
     * @param int $min
     * @param int $max
     * @return float|int
     */
    public static function rand($min = 0, $max = 1)
    {
        return ($max - $min) * mt_rand() / mt_getrandmax() + $min;
    }


    /**
     * 随机数字字符串
     *
     * @param int $length
     * @return string
     */
    public static function randomNum($length = 6)
    {
        return substr(str_shuffle(str_repeat('0123456789', $length)), 0, $length);
    }


    /**
     * 随机字符串 编码
     *
     * @param int $length
     * @param string $prefix
     * @param string $salt
     * @return string
     */
    public static function randomCode($length = 40, $prefix = '', $salt = '')
    {
        $randomStr = $prefix;

        while (strlen($randomStr) < $length) {
            $randomStr .= md5($salt . microtime(true) . str_random(20));
        }

        return substr($randomStr, 0, $length);
    }


    /**
     * 验证手机号
     *
     * @param $mobile
     * @return bool
     */
    public static function verifyMobile($mobile)
    {
        if (strlen($mobile) == 11 && preg_match('/^1\d{10}$/', $mobile)) {
            return true;
        }

        return false;
    }


    /**
     * 手机号脱敏
     *
     * @param $mobile
     * @return string
     */
    public static function enMobile($mobile)
    {
        if (strlen($mobile) == 11) {
            return substr_replace($mobile, '****', 3, 4);
        }

        return $mobile;
    }

}
