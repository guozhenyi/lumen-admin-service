<?php

namespace App\Services;

use OSS\OssClient;
use OSS\Core\OssException;

class AliOssService
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


    public function getKey($key = null)
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
            return isset($data[$key]) ? $data[$key] : '';
        }

        return $data;
    }


    /**
     * @param $path_name
     * @param $file_name
     * @return null
     * @throws \OSS\Core\OssException
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-10-24
     */
    public function upload($path_name, $file_name)
    {
        $conf = $this->getKey();

        try {
            $client = new OssClient($conf['app_key'], $conf['app_secret'], $conf['end_point']);

            // 设置建立连接的超时时间，单位秒，默认10秒。
            $client->setConnectTimeout(10);

            // 设置Socket层传输数据的超时时间，单位秒，默认5184000秒。
            $client->setTimeout(3600);

            $res = $client->uploadFile($this->getKey('bucket'), $file_name, $path_name);

            $ossCdnHost = env('OSS_SERVICE_HOST', '');

            if (app()->environment() == 'production' && !empty($ossCdnHost)) {
                // http://kx-gongsi-ceshi.oss-cn-shenzhen.aliyuncs.com/file/20181041/2018101310fa17e13ffd7f2a68a51c73cc8c87ae.jpg
                $pattern = '/http(s?):\/\/[^\/]*?\//i';
                if (isset($res['oss-request-url'])) {
                    $res['oss-request-url'] = preg_replace($pattern, $ossCdnHost, $res['oss-request-url']);
                }
                if (isset($res['info']['url'])) {
                    $res['info']['url'] = preg_replace($pattern, $ossCdnHost, $res['info']['url']);;
                }
            }

            return $res;
        } catch (OssException $e) {
            throw $e;
        }
    }


    /**
     * @param $file_url
     * @return null
     * @throws \OSS\Core\OssException
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-08
     */
    public function downFile($file_url)
    {
        $path = '/avatar/' . date('YmW');

        $avatar_path = storage_path() . '/uploads/avatar_wx.jpg';

        $ch = curl_init($file_url);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $file_content = curl_exec($ch);
        curl_close($ch);

//        ob_start();
//        readfile($file_url);
//        $file_content = ob_get_contents();
//        ob_end_clean();

        $fh = fopen($avatar_path, 'wb');
        fwrite($fh, $file_content);
        fclose($fh);

        $name = date('Ymd') . md5(microtime(true) . str_random(16));

        $file_name = trim($path, '/') . '/' . $name . '.jpg';

        $res = $this->upload($avatar_path, $file_name);

        $oss_url = null;

        if (isset($res['oss-request-url'])) {
            $oss_url = $res['oss-request-url'];
        } elseif (isset($res['info']['url'])) {
            $oss_url = $res['info']['url'];
        }

        return $oss_url;
    }



}
