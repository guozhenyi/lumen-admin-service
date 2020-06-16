<?php

namespace App\Http\Controllers\File;

use App\Models\Main\SysAsset;
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
        if ($request->hasFile('file')) {
            $data = $this->repository->uploadFile($request->file('file'));
        } elseif ($request->has('file') && is_string($request->input('file'))) {
            $data = $this->repository->uploadBlob($request->input('file'), $request->input());
        } else {
            throw new \UnexpectedValueException('请选择上传的文件', 400);
        }

        return $this->respond($data);
    }





}
