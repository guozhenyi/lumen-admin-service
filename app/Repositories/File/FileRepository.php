<?php

namespace App\Repositories\File;

use App\Support\Env;
use App\Models\main\Asset;
//use OSS\Core\OssException;
//use App\Services\AliOssService;
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

        $data = [
            'url' => ''
        ];

        $path = $this->prefix . '/' . date('Y') . '/' . date('m');

        $saveDir = Asset::model()->checkDir(Env::fileDir($this->prefix) . $path);

        $name = Asset::model()->assignFileName() . '.' . $ext;

        // 保存文件
        $upFile->move($saveDir, $name);

        $url = Env::fileDomainUrl() . $path . '/' . $name;

        Asset::model()->store([
            'md5' => $md5,
            'ext' => $ext,
            'path' => $path,
            'url' => $url,
            'origin' => $originName,
            'size' => $size,
        ]);

        $data['url'] = $url;

        return $data;
    }


    /**
     * 上传Blob式的文件
     *
     * @param string $fileEncodeString
     * @param string $server_url
     * @param array $dict
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

        $data = [
            'url' => ''
        ];

        $path = $this->prefix . '/' . date('Y') . '/' . date('m');

        $saveDir = Asset::model()->checkDir(Env::fileDir($this->prefix) . $path);

        $sfyFile->move($saveDir, $name);

        $url = Env::fileDomainUrl() . $path . '/' . $name;

        Asset::model()->store([
            'md5' => $md5,
            'ext' => $ext,
            'path' => $path,
            'url' => $url,
            'origin' => $originName,
            'size' => $size,
        ]);

        $data['url'] = $url;

        return $data;
    }



}
