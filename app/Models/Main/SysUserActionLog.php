<?php

namespace App\Models\Main;

use App\Models\Util;

class SysUserActionLog extends Base
{

    protected $table = 'sys_user_action_log';


    /**
     * @param array $aryDict
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-05-28
     */
    public function store(array $aryDict)
    {
        $aryAttr = [
            'editor_id' => 0,
            'editor_name' => '',
            'describe' => '',
            'ipv4' => '',
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = Util::handleParamValue($value);
            }
        }

        if (defined('X_CLIENT_IP') && empty($aryAttr['ipv4'])) {
            $aryAttr['ipv4'] = X_CLIENT_IP;
        }

        $this->getQuery()->insert($aryAttr);
    }


    public function signOutDescribe($sysName)
    {
        $time = date('H:i:s');

        return sprintf('%s在[%s]退出登录。', $sysName, $time);
    }


    public function addSignOutLog($sys_user_id)
    {
        $sysUser = SysUser::model()->getByUserId($sys_user_id);

        $this->store([
            'editor_id' => $sysUser->id,
            'editor_name' => $sysUser->username,
            'describe' => $this->signOutDescribe($sysUser->username),
        ]);
    }


}
