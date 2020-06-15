<?php

namespace App\Repositories\File;

use App\Support\Env;
use App\Models\main\Asset;
use OSS\Core\OssException;
use App\Services\AliOssService;
use App\Exceptions\XClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class FileRepository
{

    protected $prefix = '/uploads';


    /**
     * 上传文件
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $upFile
     * @throws \Exception
     * @return array
     */
    public function uploadFile($upFile)
    {
        if (!($upFile instanceof UploadedFile)) {
            throw new \UnexpectedValueException('文件类型错误', 400);
        }

        $md5 = md5_file($upFile->getPathname());

        $originName = $upFile->getClientOriginalName();  // 原始文件名
        // 文件扩展类型
//        $ext = strtolower($upFile->getClientOriginalExtension());
        $ext = Asset::model()->getImageExt($upFile->getPathname());

        $size = (int)$upFile->getClientSize();

        $path = $this->prefix . '/' . date('Y') . '/' . date('m');

        $saveDir = Asset::model()->checkDir(Env::fileDir($this->prefix) . $path);

        $name = Asset::model()->assignFileName() . '.' . $ext;

        // 判断阿里云OSS是否开启
        if (Env::isAliyunOssUsable()) {
            $path_name = trim($path) . '/' . $name;
            $url = $this->uploadOss($upFile->getPathname(), $path_name);
        } else {
            $url = Env::fileDomainUrl() . $path . '/' . $name;
        }

        // 本地服务器保存文件
        $upFile->move($saveDir, $name);

        Asset::model()->store([
            'md5' => $md5,
            'ext' => $ext,
            'path' => $path,
            'url' => $url,
            'origin' => $originName,
            'size' => $size,
        ]);

        return [
            'url' => $url,
        ];
    }


    /**
     * 上传Blob式的文件
     *
     * @param string $fileEncodeString
     * @param array $dict
     * @throws \Exception
     * @return array
     */
    public function uploadBlob($fileEncodeString, array $dict = [])
    {
        if (!preg_match('/data:(.+?);base64,(.*)/', $fileEncodeString, $matches)) {
            throw new XClientException('上传错误', 400);
        }

        $ext = Asset::model()->getExtensionFromMime($matches[1]);
        if (is_null($ext)) {
            throw new XClientException('不支持的文件类型', 400);
        }

        $tmpSaveDir = Asset::model()->checkDir(Env::fileDir($this->prefix) . $this->prefix . '/tmp');

        $name = Asset::model()->assignFileName() . '.' . $ext;

        $tmpFile = $tmpSaveDir . '/' . $name;

        // 暂存临时文件
        file_put_contents($tmpFile, base64_decode($matches[2]));

        $originName = 'base64'; // 原始文件名
        if (isset($dict['origin_name'])) {
            $originName = $dict['origin_name'];
        }

        $sfyFile = new SymfonyFile($tmpFile);

        $md5 = md5_file($sfyFile->getPathname());

        $size = (int)$sfyFile->getSize();

        $path = $this->prefix . '/' . date('Y') . '/' . date('m');

        $saveDir = Asset::model()->checkDir(Env::fileDir($this->prefix) . $path);

        // 判断阿里云OSS是否开启
        if (Env::isAliyunOssUsable()) {
            $path_name = trim($path) . '/' . $name;
            $url = $this->uploadOss($sfyFile->getPathname(), $path_name);
        } else {
            $url = Env::fileDomainUrl() . $path . '/' . $name;
        }

        // 本地服务器保存文件
        $sfyFile->move($saveDir, $name);

        Asset::model()->store([
            'md5' => $md5,
            'ext' => $ext,
            'path' => $path,
            'url' => $url,
            'origin' => $originName,
            'size' => $size,
        ]);

        return [
            'url' => $url,
        ];
    }


    /**
     * @param $file_path
     * @param $path_name
     * @return string
     * @throws \Exception
     *
     * @author gzy<gzyonline@hotmail.com>
     * @date 2019-07-26
     */
    protected function uploadOss($file_path, $path_name)
    {
        $res = AliOssService::instance()->upload($file_path, $path_name);

        if (!isset($res['oss_url'])) {
            throw new XServerException('未获得OSS链接地址');
        }

        return $res['oss_url'];
    }



}
