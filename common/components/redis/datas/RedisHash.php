<?php

namespace common\components\redis\datas;

/**
 * Redis Hash类型的方法，单独放置在RedisHash类中，继承 RedisBase 方法
 * 
 * 我们可以将Redis中的Hash类型看成具有String Key和String Value的map容器。
 * 所以该类型非常适合于存储值对象的信息。如Username、Password和Age等。
 * 如果Hash中包含很少的字段，那么该类型的数据也将仅占用很少的磁盘空间。每一个Hash可以存储4294967295个键值对。
 *
 * @author Administrator
 */
class RedisHash extends RedisBase {

    /**
     * 赋值命令
     * 
     * 为指定的Key设定Field/Value对，如果Key不存在，该命令将创建新Key以用于存储参数中的Field/Value对，如果参数中的Field在该Key中已经存在，则用新值覆盖其原有值。
     * 
     * @param string $key
     * @param string $field
     * @param string $value
     * @return int 1表示新的Field被设置了新值，0表示Field已经存在，用新值覆盖原有值。
     */
    public function hset($key, $field, $value) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hset($key, $field, $value);
    }

    /**
     * 当字段不存在时赋值
     * 
     * 只有当参数中的Key或Field不存在的情况下，为指定的Key设定Field/Value对，否则该命令不会进行任何操作。
     * 
     * @param string $key
     * @param string $field
     * @param string $value
     * @return int 1表示新的Field被设置了新值，0表示Key或Field已经存在，该命令没有进行任何操作。
     */
    public function hsetnx($key, $field, $value) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hsetnx($key, $field, $value);
    }

    /**
     * 设置多个字段的值
     * 
     * 逐对依次设置参数中给出的Field/Value对。
     * 如果其中某个Field已经存在，则用新值覆盖原有值。
     * 如果Key不存在，则创建新Key，同时设定参数中的Field/Value。
     * 
     * @param string $key
     * @param array $members    [field1 => value1, field2 => value2 , ...]
     * @return void
     */
    public function hmset($key, array $members) {
        $key = self::buildKey($key);
        $redis = self::getRedis();

        $param = [];
        foreach ($members as $field => $value) {
            $param[] = $field;
            $param[] = $value;
        }
        return $redis->hmset($key, ...(array) $param);
    }

    /**
     * 取值命令
     * 
     * 返回指定Key中指定Field的关联值。
     * 
     * @param string $key
     * @param string $field
     * @return string 返回参数中Field的关联值，如果参数中的Key或Field不存在，返回nil。
     */
    public function hget($key, $field) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hget($key, $field);
    }

    /**
     * 获取多个字段的值
     * 
     * 获取和参数中指定Fields关联的一组Values。
     * 如果请求的Field不存在，其值返回nil。
     * 如果Key不存在，该命令将其视为空Hash，因此返回一组nil。
     * 
     * @param string $key
     * @param string|array $fields  [field1,field2,...]
     * @return array    返回和请求Fields关联的一组Values。 [field1 => value1,field2 => value2, ...]
     */
    public function hmget($key, $fields) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        $result = $redis->hmget($key, ...(array) $fields);
        $target = [];
        if (!empty($result)) {
            foreach ($result as $i => $value) {
                $target[$fields[$i]] = $value;
            }
        }
        return $target;
    }

    /**
     * 判断字段是否存在
     * 
     * 判断指定Key中的指定Field是否存在。
     * 
     * @param string $key
     * @param string $field
     * @return int 1表示存在，0表示参数中的Field或Key不存在。
     */
    public function hexists($key, $field) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hexists($key, $field);
    }

    /**
     * 获得字段数量
     * 
     * 获取该Key所包含的Field的数量。
     * 
     * @param string $key
     * @param string $field
     * @return int  返回Key包含的Field数量，如果Key不存在，返回0。
     */
    public function hlen($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hlen($key);
    }

    /**
     * 删除字段
     * 
     * 从指定Key的Hashes Value中删除参数中指定的多个字段，如果不存在的字段将被忽略。如果Key不存在，则将其视为空Hashes，并返回0。
     * 
     * @param string $key
     * @param string|array $field
     * @return int 实际删除的Field数量。
     */
    public function hdel($key, $field) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hdel($key, ...(array) $field);
    }

    /**
     * 
     * 增加数字
     * 
     * 增加指定Key中指定Field关联的Value的值。
     * 如果Key或Field不存在，该命令将会创建一个新Key或新Field，并将其关联的Value初始化为0，之后再指定数字增加的操作。
     * 该命令支持的数字是64位有符号整型，即increment可以负数。
     * 
     * @param string $key
     * @param string $field
     * @param int $increment    指定数字增加数字，可以负数
     * @return int 返回运算后的值。
     */
    public function hincrby($key, $field, $increment = 1) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hincrby($key, $field, $increment);
    }

    /**
     * 获取指定键中所有的字段/值
     * 
     * 获取该键包含的所有Field/Value。其返回格式为一个Field、一个Value，并以此类推。
     * 
     * @param string $key
     * @return type
     */
    public function hgetall($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        $result = $redis->hgetall($key);
        $target = [];

        $count = count($result);
        for ($i = 0; $i < $count; $i += 2) {
            $target[$result[$i]] = $result[$i + 1];
        }
        return $target;
    }

    /**
     * 只获取字段名
     * 
     * 返回指定Key的所有Fields名。
     * 
     * @param string $key
     * @return array Field的列表。
     */
    public function hkeys($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hkeys($key);
    }

    /**
     * 只获取字段值
     * 
     * 返回指定Key的所有Values名。
     * 
     * @param string $key
     * @return array value的列表。
     */
    public function hvals($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->hvals($key);
    }

}
