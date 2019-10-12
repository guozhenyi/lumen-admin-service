<?php
// +----------------------------------------------------------------------
// | Created By PhpStorm.
// +----------------------------------------------------------------------
// | Author: HuKang <kang.h@kuaixun.tech>
// +----------------------------------------------------------------------
// | DateTime: 2019/3/5 13:35
// +----------------------------------------------------------------------

namespace App\Repositories\File;


use App\Models\Base;
use App\Models\main\File;
use App\Services\AliOssService;
use App\Services\UediterService;
use OSS\Core\OssException;
use App\Exceptions\XServerException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class UeditorRepository
{
    /**
     * 上传文件
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $upFile
     * @param string $tag
     * @param string $server_url
     * @throws \Exception
     * @return array
     */
    public function action_upload($upFile, $tag = 'file', $server_url = null)
    {
        if (! ($upFile instanceof UploadedFile)) {
            throw new \UnexpectedValueException('文件类型错误', 400);
        }

        if (empty($server_url)) {
            $server_url = env('FILE_SERVICE_URL'); // 默认域名
        }

        $path = '/tdzy/'. $tag . '/' . date('Ymd');
        $saveDir = storage_path() . '/uploads' . $path;

        if (!is_writable($saveDir) || !is_dir($saveDir)) {
            if (!mkdir($saveDir, 0755, true)) {
                throw new \UnexpectedValueException('folder is not exist or cannot writable', 500);
            }
        }

        $originName = $upFile->getClientOriginalName();  // 原始文件名
        $ext = strtolower($upFile->getClientOriginalExtension()); // 文件扩展类型
        $size = (int)$upFile->getClientSize();
        $mediaCode = File::model()->randomForName();
        try {
            $res = AliOssService::instance()->upload($upFile->getPathname(), trim($path, '/') . '/' . $mediaCode . '.' . $ext);

            if (isset($res['oss-request-url'])) {
                $url = $res['oss-request-url'];
            } elseif (isset($res['info']['url'])) {
                $url = $res['info']['url'];
            } else {
                throw new XServerException('未获得OSS链接地址');
            }

        } catch (OssException $e) {
            Base::model()->logger()->info('OssException:' . $e->getMessage());

            $upFile->move($saveDir, $mediaCode . '.' . $ext);
            $url = rtrim($server_url, '/') . '/media/' . $mediaCode;

        } catch (\Exception $e) {
            Base::model()->logger()->info('aliCloud: ' . $e->getCode() . '>>' . $e->getMessage());
            throw $e;
        }


        File::model()->store([
                'tag' => $tag,
                'code' => $mediaCode,
                'ext' => $ext,
                'origin_name' => $originName,
                'path' => $path,
                'url' => $url,
                'size' => $size,
        ]);

        return [
                "state"    => 'SUCCESS',
                "url"      => $url,
                "title"    => $mediaCode.'.'.$ext,
                "original" => $originName,
                "type"     => '.'.$ext,
                "size"     => $size
        ];
    }


    public function action_list($CONFIG)
    {
        return [];
        switch ($_REQUEST['action'])
        {
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $CONFIG['fileManagerAllowFiles'];
                $listSize   = $CONFIG['fileManagerListSize'];
                $path       = $CONFIG['fileManagerListPath'];
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $CONFIG['imageManagerAllowFiles'];
                $listSize   = $CONFIG['imageManagerListSize'];
                $path       = $CONFIG['imageManagerListPath'];
        }

        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
        /* 获取参数 */
        $size  = isset($_REQUEST['size']) ? htmlspecialchars($_REQUEST['size']) : $listSize;
        $start = isset($_REQUEST['start']) ? htmlspecialchars($_REQUEST['start']) : 0;
        $end   = $start + $size;

        /* 获取文件列表 */
//        $path  = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "" : "/") . $path;

        $files = $this -> _getfiles($path, $allowFiles);

        if (!count($files))
        {
            return [
                    "state" => "no match file",
                    "list"  => [],
                    "start" => $start,
                    "total" => count($files)
            ];
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = []; $i < $len && $i >= 0 && $i >= $start; $i--)
        {
            $list[] = $files[$i];
        }

        /* 返回数据 */
        $result = [
                "state" => "SUCCESS",
                "list"  => $list,
                "start" => $start,
                "total" => count($files)
        ];

        return $result;
    }


    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    private function _getfiles($path, $allowFiles, &$files = [])
    {
        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle)))
        {
            if ($file != '.' && $file != '..')
            {
                $path2 = $path . $file;
                if (is_dir($path2))
                {
                    $this -> _getfiles($path2, $allowFiles, $files);
                } else
                {
                    if (preg_match("/\.(" . $allowFiles . ")$/i", $file))
                    {
                        $files[] = [
                                'url'   => substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                                'mtime' => filemtime($path2)
                        ];
                    }
                }
            }
        }
        return $files;
    }


    public function action_crawler($CONFIG)
    {
        set_time_limit(0);
        /* 上传配置 */
        $config    = [
                "pathFormat" => $CONFIG['catcherPathFormat'],
                "maxSize"    => $CONFIG['catcherMaxSize'],
                "allowFiles" => $CONFIG['catcherAllowFiles'],
                "oriName"    => "remote.png"
        ];
        $fieldName = $CONFIG['catcherFieldName'];

        /* 抓取远程图片 */
        $list = [];
        if (isset($_POST[$fieldName]))
        {
            $source = $_POST[$fieldName];
        } else
        {
            $source = $_REQUEST[$fieldName];
        }

        foreach ($source as $imgUrl)
        {
            $item = new UediterService($imgUrl, $config, "remote");
            $info = $item -> getFileInfo();
            array_push($list, [
                    "state"    => $info["state"],
                    "url"      => $info["url"],
                    "size"     => $info["size"],
                    "title"    => htmlspecialchars($info["title"]),
                    "original" => htmlspecialchars($info["original"]),
                    "source"   => htmlspecialchars($imgUrl)
            ]);
        }

        /* 返回抓取数据 */
        return [
                'state' => count($list) ? 'SUCCESS' : 'ERROR',
                'list'  => $list
        ];
    }

}
