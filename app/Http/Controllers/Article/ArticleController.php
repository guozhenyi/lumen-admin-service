<?php

namespace App\Http\Controllers\Article;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Article\ArticleRepository;

class ArticleController extends Controller
{

    public function __construct(Request $request, ArticleRepository $repository)
    {
        $this->request = $request;
        $this->repository = $repository;
    }


    /**
     * 新增文章
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store()
    {
        $data = $this->repository->store($this->request->input());

        return $this->respond($data);
    }


}
