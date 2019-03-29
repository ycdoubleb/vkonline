<?php

namespace common\components\redis\datas;

use Yii;
use yii\base\Behavior;
use yii\redis\Connection;

/**
 * Redis 基本操作类，
 *
 * @author Administrator
 */
class RedisBase extends Behavior {

    /**
     * 这个方法中给 key 增加前缀
     * 所有与key相关的方法都要调用这个方法
     * @param $key
     * @return string
     */
    protected function buildKey($key) {
        if (strpos($key, Yii::$app->params['redis']['prefix']) === false) {
            return Yii::$app->params['redis']['prefix'] . $key;
        } else {
            return $key;
        }
    }

    /**
     * @return Connection
     */
    protected function getRedis() {
        return Yii::$app->redis;
    }

    /**
     * # exists key
     *
     * 检查key是否存在
     * @param ...string $key
     * @return int  若 key 存在，返回 1 ，否则返回 0 。多个key返回存在的总个数
     */
    public function exists(...$keys) {
        foreach ($keys as &$key) {
            $key = self::buildKey($key);
        }
        $redis = self::getRedis();
        return $redis->exists(...$keys);
    }

    /**
     * # del key [key2 key3 …]
     *
     * 删除指定数据
     *
     * @param ...string $key
     * @return int  被删除 key 的数量。
     */
    public function del(...$keys) {
        foreach ($keys as &$key) {
            $key = self::buildKey($key);
        }
        $redis = self::getRedis();
        return $redis->del(...$keys);
    }

    /**
     * # expire key seconds
     *
     * 设置 key在seconds秒后过期
     *
     * @param $key
     * @param $seconds
     * @return int
     */
    public function expire($key, $seconds) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->expire($key, $seconds);
    }

    /**
     * 设置key的过期时间搓
     * 
     * 这个命令和 EXPIREAT 命令类似，但它以毫秒为单位设置 key 的过期 unix 时间戳，而不是像 EXPIREAT 那样，以秒为单位。
     * 
     * @param string $key
     * @param int $timestamp
     * @return int  如果生存时间设置成功，返回 1 。当 key 不存在或没办法设置生存时间时，返回 0 。
     */
    public function expireat($key, $timestamp) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->expireat($key, $timestamp);
    }

    /**
     * # ttl key
     *
     * 查看key的剩余过期时间
     * 
     * 大于等于0时，表示剩余过期秒数
     * -1 表示key存在，并且没有过期时间
     * -2 表示key已经不存在了
     * 
     * @param $key
     * @return int
     */
    public function ttl($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->ttl($key);
    }

    /**
     * # dbsize
     *
     * 当前db 键的总数量
     * @return int
     */
    public function dbSize() {
        $redis = self::getRedis();
        return (int) $redis->dbsize();
    }

    /**
     * 返回值的类型
     * 
     * @param type $key
     * @return string
     */
    public function type($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->type($key);
    }

    /**
     * 返回满足给定pattern的所有key
     * 
     * pattern key 的通配模式如* ? - 
     * eg：
     * * 返回所有
     * a* 返回以a字母开始的所有key
     * h?llo matches hello, hallo and hxllo
     * h*llo matches hllo and heeeello
     * h[ae]llo matches hello and hallo, but not hillo
     * h[^e]llo matches hallo, hbllo, ... but not hello
     * h[a-b]llo matches hallo and hbllo
     * 
     * @param type $pattern
     */
    public function keys($pattern) {
        $pattern = self::buildKey($pattern);
        $redis = self::getRedis();
        return $redis->keys($pattern);
    }

}
