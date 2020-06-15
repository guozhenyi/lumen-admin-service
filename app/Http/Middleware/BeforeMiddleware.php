<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Exceptions\XClientException;
use App\Models\Main\SysTokenBlacklist;
use App\Exceptions\XTokenExpiredException;

class BeforeMiddleware
{

    /**
     * 不检查设备号的路由列表
     *
     * @return array
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-08-08
     */
    public function skipCheckDeviceRouteList()
    {
        return [
            '/',
            'v1/device',
            'v1/uploads',
        ];
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 定义全局常量
        define('X_CLIENT_IP', $request->ip());


        // 上传文件资源
//        if (preg_match('/^media\/.*/', $request->path())) {
//            return $next($request);
//        }

        $device = null;
        if ($request->headers->has('device')) {
            $device = trim($request->headers->get('device'));
        } elseif ($request->has('device')) {
            $device = trim($request->input('device'));
        }

        // 不在白名单的接口需要验证设备号
//        if (!in_array($request->path(), $this->skipCheckDeviceRouteList())) {
//            // 严格来说，这里要验证设备号是否由我们签发
//            if (empty($device) || strlen($device) != 20) {
//                throw new XClientException('设备信息异常', 402);
//            }
//        }

        // 定义常量：设备号
        if (!empty($device)) {
            define('X_DEVICE', $device);
        }

        $token = null;
        if ($request->headers->has('authorization')) {
            $token = trim($request->bearerToken());
        } elseif ($request->has('token')) {
            $token = trim($request->input('token'));
        }

        // 定义常量：token
        if (!empty($token)) {
            define('X_TOKEN', $token);
        }

        // 判断token黑名单机制
//        if (defined('X_TOKEN')) {
//            if (SysTokenBlacklist::model()->checkTokenExist($token)) {
//                $obj = SysTokenBlacklist::model()->getTokenOrNot($token);
//                if ($obj !== false && time() > $obj->expires_at) {
//                    throw new XTokenExpiredException('The token has been blacklisted');
//                }
//            }
//        }

        return $next($request);
    }
}

