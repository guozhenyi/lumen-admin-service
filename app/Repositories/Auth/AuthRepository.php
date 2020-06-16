<?php

namespace App\Repositories\Auth;

use App\Models\Main\SysDevice;
use App\Models\Main\SysMenu;
use App\Models\Main\SysRole;
use App\Models\Main\SysUser;
use App\Services\UserService;
use App\Models\Main\SysUserActionLog;
use App\Models\Main\SysTokenBlacklist;
use App\Exceptions\XClientException;

class AuthRepository
{

    /**
     * 分配设备号
     *
     * @return array
     * @throws \Exception
     */
    public function device()
    {
        do {
            $device = str_random(20);
            if (!SysDevice::model()->checkExistByDeviceId($device)) {
                break;
            }
        } while (true);

        SysDevice::model()->store([
            'device' => $device,
            'ip' => X_CLIENT_IP,
        ]);

        return [
            'device' => $device,
        ];
    }


    /**
     * 登录
     *
     * @param array $aryDict
     * @return array
     * @throws \Exception
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-09
     */
    public function auth(array $aryDict)
    {
        if (!defined('X_DEVICE')) {
            throw new XClientException('need device', 1002);
        }

        $device = X_DEVICE;

        if (empty($aryDict['verifyCode'])) {
            throw new XClientException('请填写验证码');
        }
        if (empty($aryDict['username']) || empty($aryDict['password'])) {
            throw new XClientException('请填写用户名和密码');
        }

        $username = trim($aryDict['username']);
        $password = trim($aryDict['password']);

        // 验证码
        UserService::instance()->verifyCaptcha($device, $aryDict['verifyCode']);

        $user = SysUser::model()->getByUsername($username);

        if ($user->status != SysUser::STATUS_ACTIVE) {
            throw new XClientException('该账号目前不可使用');
        }

        if (!password_verify($password, $user->password)) {
            throw new XClientException('用户名或密码不正确');
        }

        $token = UserService::instance()->generateToken($user->id);

        // 设置用户ID映射device缓存
//        UserService::instance()->setUserIdToDeviceCache($user->id, $device);

//        SysUserLog::model()->store([
//            'user_id' => $user->id,
//            'user_name' => $user->nickname,
//            'describe' => '',
//        ]);

        // 清除验证码
        UserService::instance()->delCaptcha($device);

        return [
            'token' => $token,
        ];
    }


    /**
     * 当前用户信息
     *
     * @return array
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-07-04
     */
    public function myInfo()
    {
        $user_id = X_USER_ID;

        return [
            'user' => $this->formatUserData($user_id),
            'menuList' => $this->formatMenu($user_id),
        ];
    }


    /**
     * 修改密码
     *
     * @param array $aryDict
     * @return array
     * @throws \Exception
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-13
     */
    public function changePassword(array $aryDict)
    {
        if (!isset($aryDict['password']) || strlen($aryDict['password']) == 0) {
            throw new XClientException('请输入原密码');
        }

        if (!isset($aryDict['newPassword']) || strlen($aryDict['newPassword']) == 0) {
            throw new XClientException('请输入新密码');
        }

        $password = trim($aryDict['password']);
        $newPassword = trim($aryDict['newPassword']);

        if (strlen($newPassword) != 64) {
            throw new XClientException('密码需要哈希值');
        }

        $user = SysUser::model()->getByUserId(X_USER_ID);

        if (!password_verify($password, $user->password)) {
            throw new XClientException('密码不正确');
        }

        SysUser::model()->update(X_USER_ID, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT)
        ]);

        return [
            'msg' => '修改成功'
        ];
    }


    public function signOut()
    {
        // token加入黑名单
        SysTokenBlacklist::model()->autoStore(X_TOKEN);

        SysUserActionLog::model()->addSignOutLog(X_USER_ID);

        return [
            'msg' => '操作成功'
        ];
    }


    protected function formatUserData($user_id)
    {
        $user = SysUser::model()->getByUserId($user_id);

        $role_name = '成员';
        if ($user->role_id == SysRole::SUPER_ADMIN) {
            $role_name = '系统管理员';
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'role_name' => $role_name,
        ];
    }


    public function formatMenu($user_id)
    {
        if (SysMenu::model()->isEmptyMenu()) {
            SysMenu::model()->initMenu();
        }

        $user = SysUser::model()->getByUserId($user_id);

        if ($user->role_id == SysRole::SUPER_ADMIN) {
            $aryMenu = SysMenu::model()->getAllMenu();
        } else {
            $sysRole = SysRole::model()->getRoleOrNotById($user->role_id);

            if ($sysRole === false) {
                return [];
            }

            $menuIds = (array)json_decode($sysRole->menu_ids);

            $aryMenu = SysMenu::model()->getMenuByIds($menuIds);
        }

        return SysMenu::model()->recurseMenu($aryMenu);
    }









}
