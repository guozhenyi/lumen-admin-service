<?php

namespace App\Services;

use App\Models\Util;
use App\Support\Env;
use OSS\OssClient;
use OSS\Core\OssException;
use App\Exceptions\XServerException;

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



    /**
     * @param $path_name
     * @param $file_name
     * @return null
     * @throws \OSS\Core\OssException
     * @date 2018-10-24
     */
    public function upload($path_name, $file_name)
    {
        $conf = Env::ossConf();

        try {
            $client = new OssClient($conf['app_key'], $conf['app_secret'], $conf['end_point']);

            // 设置建立连接的超时时间，单位秒，默认10秒。
            $client->setConnectTimeout(10);

            // 设置Socket层传输数据的超时时间，单位秒，默认5184000秒。
            $client->setTimeout(3600);

            $res = $client->uploadFile(Env::ossConf('bucket'), $file_name, $path_name);

            $ossCdnHost = Env::ossCdnUrl('');

//            if (app()->environment() == 'production' && !empty($ossCdnHost)) {
            if (!empty($ossCdnHost)) {
                // http://ceshi.oss-cn-shenzhen.aliyuncs.com/file/20181041/d7f2a68a51c73cc8c87ae.jpg
                $pattern = '/http(s?):\/\/[^\/]*?\//i';
                if (isset($res['oss-request-url'])) {
                    $res['oss-request-url'] = preg_replace($pattern, $ossCdnHost, $res['oss-request-url']);
                }
                if (isset($res['info']['url'])) {
                    $res['info']['url'] = preg_replace($pattern, $ossCdnHost, $res['info']['url']);;
                }
            }

            if (isset($res['oss-request-url'])) {
                $res['oss_url'] = $res['oss-request-url'];
            } elseif (isset($res['info']['url'])) {
                $res['oss_url'] = $res['info']['url'];
            }

            return $res;
        } catch (OssException $e) {
            Util::logger()->info('OssException:' . $e->getMessage());
            throw new XServerException('OSS Error:' . $e->getMessage());
        } catch (\Exception $e) {
            Util::logger()->info('Error: ' . $e->getCode() . '>>' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * @param $from_bucket
     * @param $from_path_name
     * @param $to_path_name
     * @return null
     * @throws \OSS\Core\OssException
     * @date 2019-08-05
     */
    public function copyOssObject($from_bucket, $from_path_name, $to_path_name)
    {
        $conf = Env::ossConf();

        $client = new OssClient($conf['app_key'], $conf['app_secret'], $conf['end_point']);

        // 设置建立连接的超时时间，单位秒，默认10秒。
        $client->setConnectTimeout(10);

        // 设置Socket层传输数据的超时时间，单位秒，默认5184000秒。
        $client->setTimeout(3600);

        $res = $client->copyObject($from_bucket, $from_path_name, Env::ossConf('bucket'), $to_path_name);

        $ossCdnHost = Env::ossCdnUrl('');

        // https://ceshi.oss-cn-shenzhen.aliyuncs.com/b0add5d2ec7d725.jpg

        if (!is_array($res)) {
            $res = (array)$res;
        }

        if (!empty($ossCdnHost)) {
            $res['oss_url'] = $ossCdnHost . $to_path_name;
        } elseif (preg_match('/^(https?:\/\/)([^\/]*)/i', $conf['end_point'], $matches)) {
            $schema = $matches[1];
            $host = Env::ossConf('bucket') . '.' . trim($matches[2], '/');

            $res['oss_url'] = $schema . $host . '/' . $to_path_name;
        }

        return $res;
    }



}
