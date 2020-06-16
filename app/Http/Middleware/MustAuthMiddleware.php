<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Redis;
use App\Support\Util;
use Illuminate\Http\Request;
use App\Models\Main\SysToken;
use App\Services\JwtService;
use App\Services\UserService;
use App\Exceptions\XTokenExpiredException;

class MustAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        // 检查是否传token
        if (!defined('X_TOKEN')) {
            throw new XTokenExpiredException();
        }

        $this->defineUserId($request);

        return $next($request);
    }


    /**
     * 账号异常时，白名单路由列表（不跳转登录页）
     *
     * @var array
     */
    protected $routeWhiteListEx = [
        'v1/channel',
    ];


    /**
     * 定义用户ID常量
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Exception
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-02-25
     */
    protected function defineUserId(Request $request)
    {
        // token里解析user_id
        // 通过token、device限制单一设备登录

        // 2019-02-23
        // token不判断过期时间  不用自动续新token 因为没必要
        // token 只做签名验证，防止篡改的目的，可以提取user_id，不验证过期时间
        // device sha1(token) 来保持唯一设备登录

        // 定义常量：用户ID
        if (defined('X_TOKEN')) {
            $payload = UserService::instance()->getPayloadByToken(X_TOKEN);

//            JwtService::instance()->verifyPayload($payload);

            define('X_USER_ID', $payload['sub']);
        }

        // 检查单设备登录
//        if (defined('X_USER_ID') && defined('X_DEVICE')) {
//            $this->checkSingleDeviceLogin(X_USER_ID, X_DEVICE);
//        }

    }


    protected function checkSingleDeviceLogin($user_id, $device, $timeout = 3600, $cache = true)
    {
        $redis = Util::redis();

        $cache_key = Redis::keyUserIdToDevice($user_id);

        if ($cache && $redis->exists($cache_key) && $device != $redis->get($cache_key)) {
            throw new XTokenExpiredException();
        }

        $objToken = SysToken::model()->getOrNotByUserId($user_id);

        if ($cache && $objToken !== false) {
            $redis->set($cache_key, $objToken->device, 'EX', $timeout);
        }

        if ($objToken === false || $device != $objToken->device) {
            throw new XTokenExpiredException();
        }
    }





}

