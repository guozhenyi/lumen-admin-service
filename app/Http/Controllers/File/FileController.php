<?php

namespace App\Http\Controllers\File;

use App\Models\Main\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\File\FileRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class FileController extends Controller
{

    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * 上传文件
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function upload(Request $request)
    {
        $server_url = $request->getSchemeAndHttpHost();

        if ($request->hasFile('file')) {
            $data = $this->repository->uploadFile($request->file('file'), $server_url);
        } elseif ($request->has('file') && is_string($request->input('file'))) {
            $data = $this->repository->uploadBlob($request->input('file'), $server_url, $request->input());
        } else {
            throw new \UnexpectedValueException('请选择上传的文件', 400);
        }

        return $this->respond($data);
    }


    public function showMedia($code)
    {
        $obj = File::model()->getFileByMediaId($code);

        if (stripos($obj->path, 'uploads') === false) {
            $path_dir = storage_path() . '/uploads';
        } else {
            $path_dir = storage_path();
        }

        $path_dir .= '/' . trim($obj->path, '/');

        $file_name = $path_dir . '/' . $obj->code . '.' . $obj->ext;

        if (!is_readable($file_name) || !is_file($file_name)) {
            return response()->make('Not Found', 404);
        }

        // 头像和图片不用验证登录
//        if ($obj->tag == 'avatar' || $obj->tag == 'image') {
//            return new BinaryFileResponse($file_name);
//        }

        // 图片类型都不用验证token
//        $symfonyFile = new SymfonyFile($file_name);
//
//        if (stripos($symfonyFile->getMimeType(), 'image') !== false) {
//            return new BinaryFileResponse($file_name);
//        }

        return new BinaryFileResponse($file_name);
    }


}
