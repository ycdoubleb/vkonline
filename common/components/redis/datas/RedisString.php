<?php

namespace common\components\redis\datas;

/**
 * 处理Redis对String类型数据的处理
 * 
 * 字符串类型是Redis中最为基础的数据存储类型，它在Redis中是二进制安全的，这便意味着该类型可以接受任何格式的数据，如JPEG图像数据或Json对象描述信息等。
 * 在Redis中字符串类型的Value最多可以容纳的数据长度是512M。
 *
 * @author Administrator
 */
class RedisString extends RedisBase {

    /**
     * # SET key value options
     *
     * 赋值命令
     *
     * @param $key
     * @param $value
     * @param array $options
     *
     * EX seconds -- 设置指定key的过期时间，以秒为单位
     * PX milliseconds -- 设置指定key的过期时间，以毫秒为单位
     * NX -- 只有在key不存在的情况下才设置.
     * XX -- 只有在key存在的情况下才设置.
     *
     * eg: ['NX', 'EX', '3600']
     *
     * @return bool 总是返回 true
     */
    public function set($key, $value, array $options = []) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->set($key, $value, ...(array) $options);
    }

    /**
     * # GET key
     *
     * 取值命令
     * 
     * 获取指定Key的Value。如果与该Key关联的Value不是string类型，Redis将返回错误信息，因为GET命令只能用于获取string Value。 
     * 
     * @param $key
     * @return string   与该Key相关的Value，如果该Key不存在，返回nil。
     */
    public function get($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->get($key);
    }

    /**
     * 设置新值，返回旧值
     * 
     * 原子性的设置该Key为指定的Value，同时返回该Key的原有值。和GET命令一样，该命令也只能处理string Value，否则Redis将给出相关的错误信息。
     * 
     * @param string $key           键名
     * @param string $value         值
     * @return string               返回该Key的原有值，如果该Key之前并不存在，则返回nil。
     */
    public function getset($key, $value) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->getset($key, $value);
    }

    /**
     * mget([key1, key2,…, key N])
     * 返回库中多个string的value
     * 
     * @param array $keys    
     * @return array
     */
    public function mget($keys) {
        foreach ($keys as &$key) {
            $key = self::buildKey($key);
        }
        $redis = self::getRedis();
        return $redis->mget(...(array) $keys);
    }

    /**
     * 添加string，名称为key，值为value（仅当 $key 未设置时成功）
     * 
     * @param string $key       键名
     * @param string $value     值
     * @return int
     */
    public function setnx($key, $value) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->setnx($key, $value);
    }

    /**
     * 向库中添加string，设定过期时间time
     * eg:setex('a',10,1)   设置a值为1,10秒后过期
     * 
     * @param sting $key        键名
     * @param int $time         过期时间，单位（秒）
     * @param sting $value      值
     * @return type
     */
    public function setex($key, $time, $value) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->setex($key, $time, $value);
    }

    /**
     * mset([key=>value,key=>value])
     * 批量设置多个string的值
     * 
     * @param type $keys
     * @return type
     */
    public function mset($keys) {
        $keys_arr = [];
        foreach($keys as $key => $value){
            $keys_arr[] = self::buildKey($key);
            $keys_arr[] = $value;
        }
        $redis = self::getRedis();
        return $redis->mset(...(array) $keys_arr);
    }

    /**
     * msetnx([key=>value,key=>value])
     * 当所有key都不存时，设置所有key和值
     * 
     * @param array $keys
     * @return type
     */
    public function msetnx($keys) {
        $keys_arr = [];
        foreach($keys as $key => $value){
            $keys_arr[] = self::buildKey($key);
            $keys_arr[] = $value;
        }
        $redis = self::getRedis();
        return $redis->msetnx(...(array)$keys_arr);
    }

    /**
     * 名称为key的string增1操作，返回增量后的值
     * 
     * @param string $key
     * @return int          
     */
    public function incr($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->incr($key);
    }

    /**
     * 名称为key的string增加integer
     * 
     * @param string $key
     * @param int $integer
     * @return type
     */
    public function incrby($key, $integer) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->incrby($key, $integer);
    }

    /**
     * 名称为key的string减1操作
     * 
     * @param string $key
     * @return int
     */
    public function decr($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->decr($key);
    }

    /**
     * 名称为key的string减少integer
     * 
     * @param string $key
     * @param int $integer
     * @return type
     */
    public function decrby($key, $integer) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->decrby($key, $integer);
    }

    /**
     * 名称为key的string的值附加value
     * 
     * @param string $key
     * @param string $value         附加字符
     * @return int                  附加后字符长度
     */
    public function append($key, $value) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->append($key, $value);
    }

    /**
     * 返回名称为key的string的value的子串
     * 
     * @param string $key
     * @param int $start
     * @param int $end
     * @return string
     */
    public function substr($key, $start, $end) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->substr($key, $start, $end);
    }

}
