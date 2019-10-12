<?php
// +----------------------------------------------------------------------
// | Created By PhpStorm.
// +----------------------------------------------------------------------
// | Author: HuKang <kang.h@kuaixun.tech>
// +----------------------------------------------------------------------
// | DateTime: 2019/3/15 15:25
// +----------------------------------------------------------------------

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use App\Repositories\File\UeditorRepository;

class UeditorController
{
    public function __construct(UeditorRepository $repository)
    {
        $this -> repository = $repository;
    }

    /**
     *
     * @URL v1/ueditor
     * @method  POST
     * @catalog
     * @description 百度编辑器文件上传
     *
     * options.
     *
     * @param string action  [必选]  uploadimage:上传图片，uploadscrawl：上传涂鸦，uploadvideo：上传视频，uploadfile 上传文件，listimage：列出图片，listfile：列出文件
     * @param file upfile [当action为上传文件|图片|涂鸦|视频时必传”]  需要上传的文件
     *
     * @return void
     * @remark
     * @author  HuKang <kang.h@kuaixun.tech>
     *
     */
    public function Ueditor(Request $request)
    {
        $config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/config.json')), true);
        $action = $request -> input('action');
        $tag    = 'ueditor';
        if ($request -> has('tag'))
        {
            $tag = $request -> input('tag');
        }

        $server_url = $request -> getSchemeAndHttpHost();
        switch ($action)
        {
            case 'config':
                $result = $config;
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = $this -> repository -> action_upload($request -> file('upfile'), $tag, $server_url);
                break;

            /* 列出图片 */
            case 'listimage':
                $result = $this -> repository -> action_list($config);
                break;
            /* 列出文件 */
            case 'listfile':
                $result = $this -> repository -> action_list($config);
                break;

            /* 抓取远程文件 */
            /*case 'catchimage':
                $result = $repository -> action_crawler($config);
                break;*/

            default:
                $result = [
                        'state' => '请求地址出错'
                ];
                break;
        }

        $result = json_encode($result);

        /* 输出结果 */
        if (!empty($request -> input('callback')))
        {
            if (preg_match("/^[\w_]+$/", $_GET["callback"]))
            {
                $result = htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else
            {
                $result = json_encode([
                        'state' => 'callback参数不合法'
                ]);
            }
        }
        return response() -> make($result, 200, ['content-type' => 'text/javascript']);
    }
}
