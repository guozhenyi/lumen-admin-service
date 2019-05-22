<?php

namespace App\Models;

class Param
{

    public static function model()
    {
        return new static;
    }


    /**
     * 手机号参数
     *
     * @param array $aryDict
     * @param null|string $default
     * @return string
     */
    public function getMobile(array $aryDict, $default = null)
    {
        if (isset($aryDict['mobile']) && strlen($aryDict['mobile']) > 0) {
            return strval($aryDict['mobile']);
        }

        return $default;
    }


    /**
     * 短信验证码参数
     *
     * @param array $aryDict
     * @param string|null $default
     * @return string
     */
    public function getSmsCode(array $aryDict, $default = null)
    {
        if (isset($aryDict['sms_code']) && strlen($aryDict['sms_code']) > 0) {
            return strval($aryDict['sms_code']);
        }

        return $default;
    }


    /**
     * 分页参数之当前页
     *
     * @param array $aryDict
     * @param int|null $default
     * @return int|null
     */
    public function getPage(array $aryDict, $default = null)
    {
        $value = 0;

        if (isset($aryDict['page']) && strlen($aryDict['page']) > 0) {
            $value = (int)$aryDict['page'];
        }

        if ($value > 0) {
            return $value;
        }

        return $default;
    }


    /**
     * 分页参数之每页条数
     *
     * @param array $aryDict
     * @param int|null $default
     * @return int|null
     */
    public function getPageSize(array $aryDict, $default = null)
    {
        $value = 0;

        if (isset($aryDict['page_size']) && strlen($aryDict['page_size']) > 0) {
            $value = (int)$aryDict['page_size'];
        } elseif (isset($aryDict['pageSize']) && strlen($aryDict['pageSize']) > 0) {
            $value = (int)$aryDict['pageSize'];
        }

        if ($value > 0) {
            return $value;
        }

        return $default;
    }


    /**
     * 分页参数之每页条数
     *
     * @param array $aryDict
     * @param int|null $default
     * @return int|null
     */
    public function getLimit(array $aryDict, $default = null)
    {
        if (isset($aryDict['limit']) && strlen($aryDict['limit']) > 0) {
            return (int)$aryDict['limit'];
        }

        return $default;
    }


    /**
     * 分页参数之当前ID
     *
     * @param array $aryDict
     * @param int|null $default
     * @return int|null
     */
    public function getCurId(array $aryDict, $default = null)
    {
        if (isset($aryDict['cur_id']) && strlen($aryDict['cur_id']) > 0) {
            return (int)$aryDict['cur_id'];
        }

        return $default;
    }


    /**
     * 分页参数之当前最大ID
     *
     * @param array $aryDict
     * @param int|null $default
     * @return int|null
     */
    public function getCurMaxId(array $aryDict, $default = null)
    {
        if (isset($aryDict['cur_max_id']) && strlen($aryDict['cur_max_id']) > 0) {
            return (int)$aryDict['cur_max_id'];
        }

        return $default;
    }


    /**
     * 分页参数之当前最小ID
     *
     * @param array $aryDict
     * @param int $default
     * @return int|null
     */
    public function getCurMinId(array $aryDict, $default = null)
    {
        if (isset($aryDict['cur_min_id']) && strlen($aryDict['cur_min_id']) > 0) {
            return (int)$aryDict['cur_min_id'];
        }

        return $default;
    }


    /**
     * 分页参数之排序
     *
     * @param array $aryDict
     * @param string|null $default
     * @return string|null
     */
    public function getOrder(array $aryDict, $default = null)
    {
        if (isset($aryDict['order']) && strlen($aryDict['order']) > 0) {
            $order = strtolower(trim($aryDict['order']));
            if (in_array($order, ['asc', 'desc'])) {
                return $order;
            }
        }

        return $default;
    }


    /**
     * 参数之动作
     *
     * @param array $aryDict
     * @param null $default
     * @return int|null
     */
    public function getAction(array $aryDict, $default = null)
    {
        if (isset($aryDict['action']) && strlen($aryDict['action']) > 0) {
            return (int)$aryDict['action'];
        }

        return $default;
    }


    /**
     * 参数之类型
     *
     * @param array $aryDict
     * @param null $default
     * @return int|null
     */
    public function getType(array $aryDict, $default = null)
    {
        if (isset($aryDict['type']) && strlen($aryDict['type']) > 0) {
            return (int)$aryDict['type'];
        }

        return $default;
    }


}
