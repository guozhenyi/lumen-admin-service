<?php

namespace App\Models\Main;

use App\Support\Util;
use App\Exceptions\XServerException;

class SysDevice extends Base
{

    protected $table = 'sys_device';


    /**
     * @param array $aryDict
     * @return int
     */
    public function store(array $aryDict)
    {
        $aryAttr = [
            'device' => '',
            'ipv4' => '',
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = Util::handleParamValue($value);
            }
        }

        if (empty($aryAttr['device'])) {
            throw new XServerException('缺少参数');
        }

        return $this->getQuery()->insertGetId($aryAttr);
    }


    /**
     * @param $device
     * @return bool
     */
    public function checkExistByDeviceId($device)
    {
        return $this->getQuery()
            ->where('device', $device)
            ->exists();
    }


    /**
     *
     * @param $device
     * @return object|false
     */
    public function getByDevice($device)
    {
        $obj = $this->getQuery()
            ->where('device', $device)
            ->first();

        if (!is_null($obj)) {
            return $obj;
        }

        return false;
    }


}
