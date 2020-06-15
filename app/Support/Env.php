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
    public function isAliyunOssUsable()
    {
        $switch = env('ALIYUN_OSS_ACTIVE', '');

        if ($switch === 'on') {
            return true;
        }

        return false;
    }




}
