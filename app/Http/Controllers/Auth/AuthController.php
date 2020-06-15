<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Services\UserService;
use Gregwar\Captcha\CaptchaBuilder;
use App\Http\Controllers\Controller;
use App\Exceptions\XClientException;
use App\Repositories\Auth\AuthRepository;

class AuthController extends Controller
{

    public function __construct(Request $request, AuthRepository $repository)
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
     */
    public function captcha()
    {
        if (!defined('X_DEVICE')) {
            throw new XClientException('need device', 1002);
        }

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
     * @author gzy<gzyonline@hotmail.com>
     * @date 2018-06-09
     */
    public function auth()
    {
        $data = $this->repository->auth($this->request->input());

        return $this->respond($data);
    }


    /**
     * 用户信息
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author gzy<gzyonline@hotmail.com>
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
     * @author gzy<gzyonline@hotmail.com>
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
     * @author gzy<gzyonline@hotmail.com>
     * @date 2019-04-22
     */
    public function signOut()
    {
        $data = $this->repository->signOut();

        return $this->respond($data);
    }



}
