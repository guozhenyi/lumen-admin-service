<?php

namespace App\Models\Main;

use App\Models\Base;

class SysUserLog extends Base
{

    const TABLE_NAME = 'sys_user_log';


    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        return $this->mainDb()->table(self::TABLE_NAME);
    }


    /**
     * @param array $aryDict
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-05-28
     */
    public function store(array $aryDict)
    {
        $aryAttr = [
            'user_id' => 0,
            'describe' => '',
            'ipv4' => X_CLIENT_IP,
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = $this->handleParamValue($value);
            }
        }

        $this->getQuery()->insert($aryAttr);
    }


}
