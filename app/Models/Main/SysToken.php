<?php

namespace App\Models\Main;

use App\Support\Util;
use App\Exceptions\XServerException;

class SysToken extends Base
{

    protected $table = 'sys_token';


    /**
     * @param int $user_id
     * @param array $aryDict
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-12
     */
    public function store($user_id, array $aryDict)
    {
        $aryAttr = [
            'user_id' => '',
            'device' => '',
            'token' => '',
            'ipv4' => '',
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = Util::handleParamValue($value);
            }
        }

        $aryAttr['user_id'] = $user_id;

        if (empty($aryAttr['token_hash']) && isset($aryDict['token'])) {
            $aryAttr['token_hash'] = sha1($aryDict['token']);
        }

        if (empty($aryAttr['device']) || empty($aryAttr['token'])) {
            throw new XServerException('缺少参数');
        }

        $this->getQuery()->insert($aryAttr);
    }


    /**
     * @param $user_id
     * @param array $aryDict
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-02-25
     */
    public function update($user_id, array $aryDict)
    {
        $fields = [
            'device',
            'token_hash',
            'ipv4',
            'updated_at'
        ];

        $aryAttr = [];

        foreach ($aryDict as $k => $val) {
            if (in_array($k, $fields)) {
                $aryAttr[$k] = $val;
            }
        }

        if (empty($aryAttr['token_hash']) && isset($aryDict['token'])) {
            $aryAttr['token_hash'] = sha1($aryDict['token']);
        }

        if ($this->checkExistByUserId($user_id)) {
            if (!isset($aryAttr['updated_at'])) {
                $aryAttr['updated_at'] = date('Y-m-d H:i:s');
            }

            $this->getQuery()
                ->where('user_id', $user_id)
                ->update($aryAttr);
        } else {
            $this->store($user_id, $aryAttr);
        }
    }

    /**
     * @param $user_id
     * @return bool
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-12
     */
    public function checkExistByUserId($user_id)
    {
        $obj = $this->getQuery()
            ->where('user_id', $user_id)
            ->first();

        if (!is_null($obj)) {
            return true;
        }

        return false;
    }


    public function getOrNotByUserId($user_id)
    {
        $adminToken = $this->getQuery()
            ->where('user_id', $user_id)
            ->first();

        if (!is_null($adminToken)) {
            return $adminToken;
        }

        return false;
    }



}
