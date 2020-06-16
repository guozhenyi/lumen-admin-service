<?php

namespace App\Services;

use App\Support\Util;
use App\Models\Redis;
use App\Models\Main\SysToken;
use App\Exceptions\XJwtException;
use App\Exceptions\XClientException;
use App\Exceptions\XTokenExpiredException;

class UserService
{

    /**
     * @var static
     */
    protected static $instance;

    /**
     * 实例化
     *
     * @return static
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }


    public function setCaptcha($device, $verifyCode, $timeout = 300)
    {
        $redis = Util::redis();
        
        $key = Redis::keyCaptcha($device);

        $redis->set($key, $verifyCode, 'EX', $timeout);
    }


    public function delCaptcha($device)
    {
        $redis = Util::redis();
        
        $key = Redis::keyCaptcha($device);

        $redis->del([$key]);
    }


    public function verifyCaptcha($device, $inputCode)
    {
//        if (app()->environment() == 'local') {
//            return;
//        }

        $key = Redis::keyCaptcha($device);
        $redis = Util::redis();

        if (!$redis->exists($key)) {
            throw new XClientException('验证码已过期');
        }

        $knownStr = $redis->get($key);
        $inputCode = strtoupper(trim($inputCode));

        if (empty($inputCode) || empty($knownStr) || !hash_equals($knownStr, $inputCode)) {
            throw new XClientException('验证码错误');
        }
    }


    /**
     * 生成token
     *
     * @param $user_id
     * @return string
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-07-10
     */
    public function generateToken($user_id)
    {
        $token = JwtService::instance()->tokenByUserId($user_id);

//        SysToken::model()->update($user_id, [
//            'device' => X_DEVICE,
//            'token' => $token,
//            'ip' => X_CLIENT_IP,
//        ]);

        return $token;
    }


    /**
     *
     * @param $token
     * @param bool $cache
     * @param int $timeout
     * @return array
     * @throws \Exception
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-05
     */
    public function getPayloadByToken($token, $cache = true, $timeout = 3600)
    {
        $redis = Util::redis();
        $key = Redis::keyTokenToPayload(sha1($token));

        if ($cache && $redis->exists($key)) {
            return json_decode($redis->get($key), true);
        }

        try {
            $payload = JwtService::instance()->decode($token);

            // 过期30天，需重新登录
            if (isset($payload['iat']) && time() > ($payload['iat'] + 3600 * 24 * 30)) {
                throw new XJwtException('Token has expired and can no longer be refreshed');
            }

            if ($cache) {
                $redis->set($key, json_encode($payload, JSON_UNESCAPED_UNICODE), 'EX', $timeout);
            }

            return $payload;
        } catch (XJwtException $e) {
            throw new XTokenExpiredException($e->getMessage());
        }
    }

    /**
     * 设置用户ID映射device缓存
     *
     * @param $user_id
     * @param $device
     * @param int $timeout
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-02-25
     */
    public function setUserIdToDeviceCache($user_id, $device, $timeout = 3600)
    {
        $redis = Util::redis();
        $key = Redis::keyUserIdToDevice($user_id);
        $redis->set($key, $device, 'EX', $timeout);
    }



    /**
     * 验证http请求公共参数
     * @param $error
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-02-26
     */
    public function verifyReqParam($error = '无效的请求')
    {
        $aryDict = request()->input();

        if (app()->environment() != 'production') {
            return;
        }

        if (!defined('X_DEVICE')) {
            return;
        }

        $aryAttr = [];
        foreach ($aryDict as $key => $value) {
            if (starts_with($key, 'k_')) {
                $aryAttr[$key] = urldecode($value);
            }
        }

        ksort($aryAttr);

        $concatString = '';
        foreach ($aryAttr as $key => $value) {
            $concatString .= $key . $value;
        }

        $newSign = strtoupper(md5(X_DEVICE . strtoupper(md5($concatString))));

        if (empty($aryDict['sign']) || !hash_equals($aryDict['sign'], $newSign)) {
            throw new XClientException($error);
        }
    }


}
