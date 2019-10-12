<?php

namespace App\Models\Main;

use App\Models\Base;

class File extends Base
{

    const TABLE_NAME = 'file';

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        return $this->mainDb()->table(self::TABLE_NAME);
    }


    /**
     * 存储文件到数据库
     *
     * @param array $dict
     * @return int
     */
    public function store(array $dict)
    {
        $aryAttr = [
            'code' => '',
            'ext' => '',
            'path' => '',
            'url' => '',
            'origin' => '',
            'size' => '',
        ];

        foreach ($dict as $k => $val) {
            if (array_key_exists($k, $aryAttr)) {
                if (is_array($val)) {
                    $aryAttr[$k] = json_encode($val, JSON_UNESCAPED_UNICODE);
                } elseif (is_string($val)) {
                    $aryAttr[$k] = trim($val);
                } else {
                    $aryAttr[$k] = $val;
                }
            }
        }

        $aryAttr['created_at'] = date('Y-m-d H:i:s');

        return $this->getQuery()->insertGetId($aryAttr);
    }


    public function getFileByMediaId($media_code)
    {
        $obj = $this->getQuery()
            ->where('code', $media_code)
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
     * @param $mime
     * @return string|null
     */
    public function getExtensionFromMime($mime)
    {
        $ext = null;
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
