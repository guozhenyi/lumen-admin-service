<?php

namespace App\Models\Main;

use App\Models\Util;

class SysAsset extends Base
{

    protected $table = 'sys_asset';


    /**
     * 存储文件到数据库
     *
     * @param array $dict
     * @return int
     */
    public function store(array $dict)
    {
        $aryAttr = [
            'md5' => '',
            'ext' => '',
            'path' => '',
            'url' => '',
            'origin' => '',
            'size' => '',
        ];

        foreach ($dict as $k => $val) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = Util::handleParamValue($val);
            }
        }

        $aryAttr['create_at'] = time();

        return $this->getQuery()->insertGetId($aryAttr);
    }


    public function getFileByCode($code)
    {
        $obj = $this->getQuery()
            ->where('md5', $code)
            ->first();

        if (is_null($obj)) {
            throw new \UnexpectedValueException('你所访问的资源不存在', 404);
        }

        return $obj;
    }


    public function formatFileSize($size)
    {
        $f_size = '';
        if ($size < 1024) {
            $f_size = $size . 'B';
        } elseif ($size / 1024 < 1024) {
            $f_size = bcdiv($size, '1024') . 'KB';
        } elseif ($size / (1024 * 1024) < 1024) {
            $f_size = bcdiv($size, bcmul('1024', '1024'), 1) . 'MB';
        } elseif ($size / (1024 * 1024 * 1024) < 1024) {
            $f_size = bcdiv($size, bcmul('1024', bcmul('1024', '1024')), 1) . 'GB';
        }

        return $f_size;
    }


    /**
     * @param string $file
     * @param null $default
     * @return null|string
     */
    public function getImageExt($file, $default = null)
    {
        if (function_exists('exif_imagetype')) {
            $ext = $this->getImageExtByExif($file, $default);
        } else {
            $ext = $this->getImageExtBySize($file, $default);
        }

        return $ext;
    }


    /**
     * @param string $file
     * @param null $default
     * @return null|string
     */
    public function getImageExtByExif($file, $default = null)
    {
        $mask = exif_imagetype($file);

        if ($mask === false) {
            return $default;
        }

        switch ($mask) {
            case IMAGETYPE_GIF:
                $ext = 'gif';
                break;
            case IMAGETYPE_JPEG:
                $ext = 'jpg';
                break;
            case IMAGETYPE_PNG:
                $ext = 'png';
                break;
            case IMAGETYPE_BMP:
                $ext = 'bmp';
                break;
            default:
                $ext = $default;
                break;
        }

        return $ext;
    }


    /**
     * @param string $file
     * @param null $default
     * @return null|string
     */
    public function getImageExtBySize($file, $default = null)
    {
        $size = getimagesize($file);

        if ($size === false) {
            return $default;
        }

        if (isset($size['mime'])) {
            $ext = $this->getExtensionFromMime($size['mime']);
        } else {
            $ext = $default;
        }

        return $ext;
    }


    /**
     * @param string $mime
     * @param null $default
     * @return null|string
     */
    public function getExtensionFromMime($mime, $default = null)
    {
        $ext = $default;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $ext = 'jpg';
                break;
            case 'image/png':
                $ext = 'png';
                break;
            case 'image/gif':
                $ext = 'gif';
                break;
            case 'image/webp':
                $ext = 'webp';
                break;
            case 'image/bmp':
                $ext = 'bmp';
                break;
            case 'image/x-icon':
                $ext = 'ico';
                break;
            default:
                break;
        }

        return $ext;
    }


    public function checkDir($dir)
    {
        if (!is_dir($dir) || !is_writable($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \UnexpectedValueException('folder is not exist or cannot writable', 500);
            }
        }

        return realpath($dir);
    }


    public function assignFileName($len = 15)
    {
        return substr(time() . Util::randomNum(10), 0, $len);
    }
    

    public function randomForName()
    {
        return date('Ymd') . $this->randomForMd5();
    }

    public function randomForMd5()
    {
        $string = date('Y-m-d H:i:s') . str_random(16);

        return md5($string);
    }

    public function randomForSha1()
    {
        $string = date('Y-m-d H:i:s') . str_random(16);

        return sha1($string);
    }


}
