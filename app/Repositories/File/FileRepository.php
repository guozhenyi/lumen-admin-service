<?php

namespace App\Repositories\File;

use App\Models\Base;
use App\Models\main\File;
use OSS\Core\OssException;
use App\Services\AliOssService;
use App\Exceptions\XServerException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class FileRepository
{

    /**
     * 上传文件
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $upFile
     * @param string $server_url
     * @throws \Exception
     * @return array
     */
    public function uploadFile($upFile, $server_url = null)
    {
        if (!($upFile instanceof UploadedFile)) {
            throw new \UnexpectedValueException('文件类型错误', 400);
        }

        if (empty($server_url)) {
            $server_url = env('FILE_SERVICE_URL'); // 默认域名
        }

        $path = '/uploads/danpian/' . date('Ymd');
        $saveDir = storage_path() . '/' . $path;

        if (!is_writable($saveDir) || !is_dir($saveDir)) {
            if (!mkdir($saveDir, 0755, true)) {
                throw new \UnexpectedValueException('folder is not exist or cannot writable', 500);
            }
        }

        $code = File::model()->randomForName();
        $originName = $upFile->getClientOriginalName();  // 原始文件名
        $ext = strtolower($upFile->getClientOriginalExtension()); // 文件扩展类型
        $size = (int)$upFile->getClientSize();


        try {
            $res = AliOssService::instance()->upload($upFile->getPathname(), trim($path, '/') . '/' . $code . '.' . $ext);

            if (isset($res['oss-request-url'])) {
                $url = $res['oss-request-url'];
            } elseif (isset($res['info']['url'])) {
                $url = $res['info']['url'];
            } else {
                throw new XServerException('未获得OSS链接地址');
            }

        } catch (OssException $e) {
            Base::model()->logger()->info('OssException:' . $e->getMessage());

            $upFile->move($saveDir, $code . '.' . $ext);
            $url = rtrim($server_url, '/') . '/media/' . $code;

        } catch (\Exception $e) {
            Base::model()->logger()->info('aliCloud: ' . $e->getCode() . '>>' . $e->getMessage());
            throw $e;
        }

        File::model()->store([
            'code' => $code,
            'ext' => $ext,
            'path' => $path,
            'url' => $url,
            'origin' => $originName,
            'size' => $size,
        ]);

        return [
            'code' => $code,
//            'origin' => $originName,
            'url' => $url,
        ];
    }


    /**
     * 上传Blob式的文件
     *
     * @param string $fileEncodeString
     * @param string $server_url
     * @param array $dict
     * @return array
     */
    public function uploadBlob($fileEncodeString, $server_url = null, array $dict = [])
    {
        $path = '/uploads/' . date('Ymd');

        $saveDir = storage_path() . $path;

        if (!is_writable($saveDir) || !is_dir($saveDir)) {
            if (!mkdir($saveDir, 0755, true)) {
                throw new \UnexpectedValueException('folder is not exist or cannot writable', 500);
            }
        }

        $code = File::model()->randomForName();

        if (preg_match('/data:(.+?);base64,(.*)/', $fileEncodeString, $matches)) {
            $ext = File::model()->getExtensionFromMime($matches[1]);
            if (is_null($ext)) {
                throw new \UnexpectedValueException('未知文件类型', 400);
            }

            $fileName = $code . '.' . $ext;
            file_put_contents($saveDir . '/' . $fileName, base64_decode($matches[2]));
        } else {
            throw new \UnexpectedValueException('上传错误', 400);
        }

        $originName = $fileName;  // 原始文件名
        if (isset($dict['origin_name'])) {
            $originName = $dict['origin_name'];
        }

        $symfonyFile = new SymfonyFile($saveDir . '/' . $fileName);

        $size = (string)$symfonyFile->getSize();

        if (empty($server_url)) {
            $server_url = env('FILE_SERVICE_URL'); // 默认域名
        }

        $url = rtrim($server_url, '/') . '/media/' . $code;

        File::model()->store([
            'code' => $code,
            'ext' => $ext,
            'path' => $path,
            'url' => $url,
            'origin' => $originName,
            'size' => $size,
        ]);

        return [
            'code' => $code,
            'url' => $url,
        ];
    }


}
