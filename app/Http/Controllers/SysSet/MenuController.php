<?php

namespace App\Http\Controllers\SysSet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\SysSet\MenuRepository;

class MenuController extends Controller
{

    public function __construct(Request $request, MenuRepository $repository)
    {
        $this->request = $request;
        $this->repository = $repository;
    }


    public function index()
    {
        $data = $this->repository->index();

        return $this->respond($data);
    }


    public function store()
    {
        $data = $this->repository->store($this->request->input());

        return $this->respond($data);
    }


    public function update()
    {
        $data = $this->repository->update($this->request->input());

        return $this->respond($data);
    }


    public function destroy()
    {
        $data = $this->repository->destroy($this->request->input());

        return $this->respond($data);
    }


    public function parent()
    {
        $data = $this->repository->parent($this->request->input());

        return $this->respond($data);
    }


    public function sibling()
    {
        $data = $this->repository->sibling($this->request->input());

        return $this->respond($data);
    }




}
