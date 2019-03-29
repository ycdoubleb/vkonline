<?php

namespace common\components\redis;

use common\components\redis\datas\RedisBase;
use common\components\redis\datas\RedisHash;
use common\components\redis\datas\RedisList;
use common\components\redis\datas\RedisSet;
use common\components\redis\datas\RedisSortedSet;
use common\components\redis\datas\RedisString;
use yii\base\Component;

/**
 * Redis 服务组件
 * 
 * RedisBase
 * @method mixed exists(...$keys) 检查key是否存在
 * @method mixed del(...$keys) 删除指定数据
 * @method mixed expire($key, $seconds) 设置 key在seconds秒后过期
 * @method mixed expireat($key, $timestamp) 设置key的过期时间搓
 * @method mixed ttl($key) 查看key的剩余过期时间
 * @method mixed dbSize() 当前db 键的总数量
 * @method mixed type($key) 返回值的类型
 * @method mixed keys($pattern) 返回满足给定pattern的所有key
 * 
 * RedisString
 * @method mixed set($key, $value, array $options = []) 赋值命令
 * @method mixed get($key) 取值命令
 * @method mixed getset($key, $value) 设置新值，返回旧值
 * @method mixed mget($keys) 返回库中多个string的value,eg：mget([key1, key2,…, key N])
 * @method mixed setnx($key, $value) 添加string，名称为key，值为value（仅当 $key 未设置时成功）
 * @method mixed setex($key, $time, $value) 向库中添加string，设定过期时间time
 * @method mixed mset($keys) 批量设置多个string的值 eg:mset([key=>value,key=>value])
 * @method mixed msetnx($keys) 当所有key都不存时，设置所有key和值 eg:msetnx([key=>value,key=>value])
 * @method mixed incr($key) 名称为key的string增1操作，返回增量后的值
 * @method mixed incrby($key, $integer) 名称为key的string增加integer
 * @method mixed decr($key) 名称为key的string减1操作
 * @method mixed decrby($key, $integer) 名称为key的string减少integer
 * @method mixed append($key, $value) 名称为key的string的值附加value
 * @method mixed substr($key, $start, $end) 返回名称为key的string的value的子串
 * 
 * RedisSet
 * @method mixed sadd($key, $members) 增加元素，向集合添加一个或多个成员
 * @method mixed spop($key) 从集合中弹出一个元素
 * @method mixed smove($srckey, $dstkey, $member) 将元素从一个集合转到另一个集合 
 * @method mixed srem($key, $members) 移除集合中一个或多个成员 
 * @method mixed sismember($key, $member) 判断元素是否在集合中
 * @method mixed smembers($key) 返回集合中的所有成员
 * @method mixed srandmember($key, $count = null) 随机获得集合中的元素
 * @method mixed scard($key) 获得集合中元素个数
 * @method mixed sinter($key, ...$keys) 集合间交集运算
 * @method mixed sinterstore($dstkey, ...$keys) 集合间交集运算并将结果存储
 * @method mixed sunion($key, ...$keys) 集合间并集运算
 * @method mixed sunionstore($dstkey, ...$keys) 集合间并集运算并将结果存储
 * @method mixed sdiff($key, ...$keys) 集合间差集运算
 * @method mixed sdiffstore($dstkey, ...$keys) 集合间差集运算并将结果存储
 * 
 * RedisSortedSet
 * @method mixed zadd($key, array $members) 增加元素
 * @method mixed zcard($key) 获得集合中元素个数
 * @method mixed zcount($key, $min, $max) 获得指定分数范围内的元素个数
 * @method mixed zscore($key, $member) 获得元素的分数
 * @method mixed zincrby($key, $increment, $member) 增加某个元素的分数
 * @method mixed zrange($key, $start, $stop, $WITHSCORES = false) 获得排名在某个范围的元素列表
 * @method mixed zrevrange($key, $start, $stop, $WITHSCORES = false) 获得排名在某个范围的元素列表（元素分数从大到小排序） 
 * @method mixed zrangebyscore($key, $min, $max, $WITHSCORES = false, $LIMIT = false, $offset = null, $count = null) 获得指定分数范围的元素 
 * @method mixed zrevrangebyscore($key, $max, $min, $WITHSCORES = false, $LIMIT = false, $offset = null, $count = null) 获得指定分数范围的元素（元素分数从大到小排序）
 * @method mixed zrank($key, $member) 获得元素的排名
 * @method mixed zrevrank($key, $member) 获得元素的排名（元素分数从大到小排序）
 * @method mixed zrem($key, $member) 删除一个或多个元素
 * @method mixed zremrangebyrank($key, $start, $stop) 按照排名范围删除元素
 * @method mixed zremrangebyscore($key, $min, $max) 按照分数范围删除元素
 * 
 * RedisList
 * @method mixed rpush($key, $values) 向列表右边添加元素
 * @method mixed lpush($key, $values) 向列表左边添加元素
 * @method mixed llen($key) 获取列表中元素的个数
 * @method mixed lrange($key, $start = 0, $end = -1) 获得列表片段
 * @method mixed ltrim($key, $start, $end) 只保留列表指定片段
 * @method mixed lindex($key, $index) 获取指定索引的元素值
 * @method mixed lset($key, $index, $value) 给名称为key的list中index位置的元素赋值
 * @method mixed lrem($key, $count, $value) 删除列表中指定的值。在指定Key关联的链表中，删除前count个值等于value的元素。
 * @method mixed lpop($key) 从列表左边弹出元素
 * @method mixed rpop($key) 从列表右边弹出元素
 * @method mixed rpoplpush($srckey, $dstkey) 将元素从一个列表转到另一个列表
 * 
 * RedisHash
 * @method mixed hset($key, $field, $value) 赋值命令
 * @method mixed hsetnx($key, $field, $value) 当字段不存在时赋值
 * @method mixed hmset($key, array $members) 设置多个字段的值
 * @method mixed hget($key, $field) 取值命令
 * @method mixed hmget($key, $fields) 获取多个字段的值
 * @method mixed hexists($key, $field) 判断字段是否存在
 * @method mixed hlen($key) 获得字段数量
 * @method mixed hdel($key, $field) 删除字段
 * @method mixed hincrby($key, $field, $increment = 1) 增加数字
 * @method mixed hgetall($key) 获取指定键中所有的字段/值
 * @method mixed hkeys($key) 只获取字段名
 * @method mixed hvals($key) 只获取字段值
 * 
 * @author wskeee
 */
class Redis extends Component {

    public function behaviors() {
        return [
            RedisBase::class,
            RedisString::class,
            RedisSet::class,
            RedisSortedSet::class,
            RedisList::class,
            RedisHash::class,
        ];
    }

}
