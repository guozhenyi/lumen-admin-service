<?php

namespace App\Models\Main;

use App\Models\Base;
use App\Models\Redis;
use App\Exceptions\XServerException;

class SysTokenBlacklist extends Base
{

    const TABLE_NAME = 'sys_token_blacklist';


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
     * @date 2019-03-05
     */
    public function store(array $aryDict)
    {
        $fields = [
            'token_hash',
            'expires_at',
        ];

        $aryAttr = [];
        foreach ($aryDict as $k => $value) {
            if (in_array($k, $fields)) {
                if (is_array($value)) {
                    $aryAttr[$k] = json_encode($value, JSON_UNESCAPED_UNICODE);
                } elseif (is_string($value)) {
                    $aryAttr[$k] = trim($value);
                } else {
                    $aryAttr[$k] = $value;
                }
            } else {
                throw new XServerException('缺少参数');
            }
        }

        $this->getQuery()->insert($aryAttr);
    }


    public function autoStore($token)
    {
        if (!$this->checkTokenExist($token)) {
            $this->store([
                'token_hash' => sha1($token),
                'expires_at' => time() + 5
            ]);
        }

        $this->delKeyBlackListTokenBool($token);
    }


    public function checkTokenExist($token, $cache = true, $timeout = 3600)
    {
        $hash = sha1($token);

        $key = Redis::model()->keyBlacklistTokenBool($hash);

        if ($cache && $this->redis()->exists($key)) {
            $value = $this->redis()->get($key);
            return $value == 'true' ? true : false;
        }

        $exist = $this->getQuery()
            ->where('token_hash', $hash)
            ->exists();

        if ($cache && $exist) {
            $this->redis()->set($key, 'true', 'EX', $timeout);
        }

        if ($cache && !$exist) {
            $this->redis()->set($key, 'false', 'EX', 10);
        }

        return $exist;
    }

    // 删除缓存key，以重建缓存
    public function delKeyBlackListTokenBool($token)
    {
        $key = Redis::model()->keyBlacklistTokenBool(sha1($token));
        $this->redis()->del([$key]);
    }


    /**
     * 获得黑名单token信息
     *
     * @param $token
     * @param int $timeout
     * @return object|false
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-04-22
     */
    public function getTokenOrNot($token, $timeout = 3600)
    {
        $hash = sha1($token);

        $key = Redis::model()->keyBlacklistTokenData($hash);

        if ($this->redis()->exists($key)) {
            return json_decode($this->redis()->get($key));
        }

        $obj = $this->getQuery()
            ->where('token_hash', $hash)
            ->first();

        if (!is_null($obj)) {
            // 构建黑名单token数据缓存
            $this->redis()->set($key, json_encode($obj, JSON_UNESCAPED_UNICODE), 'EX', $timeout);
            return $obj;
        }

        return false;
    }



}
