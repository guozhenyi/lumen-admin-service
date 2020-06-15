<?php

namespace App\Support;

use App\Exceptions\XServerException;

class Env
{

    public static function create()
    {
        return new static;
    }


    /**
     * 获得上传文件目录
     *
     * @param string $prefix
     * @return mixed|string
     */
    public static function fileDir($prefix = '/uploads')
    {
        $dir = env('FILE_UPLOAD_DIR');

        if (empty($dir)) {
            $dir = base_path() . '/public';
        }

        $path = $dir . $prefix;

        if (!is_dir($path) || !is_writable($path)) {
            throw new XServerException('File Dir is not Found or not writable');
        }

        return $dir;
    }


    /**
     * 获得文件域名地址
     *
     * @return string
     */
    public static function fileDomainUrl()
    {
        $url = env('FILE_DOMAIN_URL');

        if (empty($url)) {
            $url = request()->getSchemeAndHttpHost();
        }

        return $url;
    }


    /**
     * 阿里云OSS是否启用
     *
     * @return bool
     */
    public static function isAliyunOssUsable()
    {
        $switch = env('ALIYUN_OSS_ACTIVE', '');

        if ($switch === 'on') {
            return true;
        }

        return false;
    }


    /**
     * 获得OSS配置信息
     *
     * @param null $key
     * @param string $default
     * @return array|string|mixed
     */
    public static function ossConf($key = null, $default = '')
    {
        $app_key = env('ALIYUN_OSS_KEY', '');
        $app_secret = env('ALIYUN_OSS_SECRET', '');
        $end_point = env('ALIYUN_OSS_ENDPOINT', '');
        $bucket = env('ALIYUN_OSS_BUCKET', '');

        $data = [
            'app_key' => $app_key,
            'app_secret' => $app_secret,
            'end_point' => $end_point,
            'bucket' => $bucket
        ];

        if (!is_null($key)) {
            return isset($data[$key]) ? $data[$key] : $default;
        }

        return $data;
    }


    /**
     * 获得OSS CDN 域名
     *
     * @param null $default
     * @return mixed|null
     */
    public static function ossCdnUrl($default = null)
    {
        $url = env('OSS_CDN_URL', '');

        if (empty($url)) {
            return $default;
        }

        $url = rtrim($url, '/') . '/';

        return $url;
    }


}
