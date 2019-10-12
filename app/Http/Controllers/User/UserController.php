<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Services\UserService;
use Gregwar\Captcha\CaptchaBuilder;
use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;

class UserController extends Controller
{

    public function __construct(Request $request, UserRepository $repository)
    {
        $this->request = $request;
        $this->repository = $repository;
    }


    /**
     * 分配设备号
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function device()
    {
        $data = $this->repository->device();

        return $this->respond($data);
    }


    /**
     * 验证码
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-04
     */
    public function captcha()
    {
        $device = X_DEVICE;

        //生成验证码图片的Builder对象，配置相应属性
        $builder = new CaptchaBuilder();
        //设置验证码的内容
        $phrase = strtoupper(substr($builder->getPhrase(), 0, 4));

        $builder->setPhrase($phrase);

        //可以设置图片宽高及字体
        $builder->build(140, 36);

        // 缓存图形验证码
        UserService::instance()->setCaptcha($device, $phrase);

        // 生成图片
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type: image/jpeg');
        $builder->output();

        return;
    }


    /**
     * 账号登录
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-09
     */
    public function authenticate()
    {
        $data = $this->repository->authenticate($this->request->input());

        return $this->respond($data);
    }


    /**
     * 用户信息
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-05
     */
    public function myInfo()
    {
        $data = $this->repository->myInfo();

        return $this->respond($data);
    }


    /**
     * 修改密码
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-13
     */
    public function changePassword()
    {
        $data = $this->repository->changePassword($this->request->input());

        return $this->respond($data);
    }


    /**
     * 退出登录
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-04-22
     */
    public function signOut()
    {
        $data = $this->repository->signOut();

        return $this->respond($data);
    }



}
