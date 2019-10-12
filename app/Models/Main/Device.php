<?php

namespace App\Models\Main;

use App\Models\Base;
use App\Exceptions\XServerException;

class Device extends Base
{

    const TABLE_NAME = 'device';


    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        return $this->mainDb()->table(self::TABLE_NAME);
    }


    /**
     * @param array $aryDict
     * @return int
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-14
     */
    public function store(array $aryDict)
    {
        $aryAttr = [
            'device' => '',
            'ipv4' => '',
        ];

        foreach ($aryDict as $k => $value) {
            if (array_key_exists($k, $aryAttr)) {
                $aryAttr[$k] = $this->handleParamValue($value);
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
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-14
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
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-01-22
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
