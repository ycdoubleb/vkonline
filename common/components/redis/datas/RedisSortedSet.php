<?php

namespace common\components\redis\datas;

/**
 * Redis 有序集合类型的方法，单独放置在RedisSortedSet类中，继承 RedisBase 方法
 *
 * @author Administrator
 */
class RedisSortedSet extends RedisBase {

    /**
     * 增加元素
     * 
     * 添加参数中指定的所有成员及其分数到指定key的Sorted Set中，在该命令中我们可以指定多组score/member作为参数。如果在添加时参数中的某一成员已经存在，该命令将更新此成员的分数为新值，同时再将该成员基于新值重新排序。
     * 如果键不存在，该命令将为该键创建一个新的Sorted Set Value，并将score/member对插入其中。
     * 如果该键已经存在，但是与其关联的Value不是Sorted Set类型，相关的错误信息将被返回。
     * 
     * @param $key
     * @param array $members
     * [
     *     'value1' => score1,
     *     'value2' => score2,
     *     'value3' => score3,
     * ]
     *
     * @return int
     */
    public function zadd($key, array $members) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        $param = [];

        foreach ($members as $value => $score) {
            $param[] = $score;
            $param[] = $value;
        }
        return (int) $redis->zadd($key, ...(array) $param);
    }

    /**
     * 获得集合中元素个数
     * 
     * 获取与该Key相关联的Sorted Set中包含的成员数量。
     * 
     * @param string $key
     * @return int 返回Sorted Set中的成员数量，如果该Key不存在，返回0。
     */
    public function zcard($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->zcard($key);
    }

    /**
     * 获得指定分数范围内的元素个数
     * 
     * 该命令用于获取分数(score)在min和max之间的成员数量。
     * 针对min和max参数需要额外说明的是，-inf和+inf分别表示Sorted-Sets中分数的最高值和最低值。
     * 缺省情况下，min和max表示的范围是闭区间范围，即min <= score <= max内的成员将被返回。
     * 然而我们可以通过在min和max的前面添加"("字符来表示开区间，如(min max表示min < score <= max，而(min (max表示min < score < max。
     * 
     * @param string $key
     * @param string $min 最小分数 默认包括 $min ，不包括："($min"
     * @param string $max 最大分数 默认包括 $max ，不包括："($max"
     * @return int 分数指定范围内成员的数量。
     */
    public function zcount($key, $min, $max) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return (int) $redis->zcount($key, $min, $max);
    }

    /**
     * 获得元素的分数
     * 
     * 获取指定Key的指定成员的分数。
     * 
     * @param string $key
     * @param string $member
     * @return string 如果该成员存在，以字符串的形式返回其分数，否则返回nil
     */
    public function zscore($key, $member) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zscore($key, $member);
    }

    /**
     * 增加某个元素的分数
     * 
     * 该命令将为指定Key中的指定成员增加指定的分数。
     * 如果成员不存在，该命令将添加该成员并假设其初始分数为0，此后再将其分数加上increment。
     * 如果Key不存在，该命令将创建该Key及其关联的Sorted Set，并包含参数指定的成员，其分数为increment参数。
     * 如果与该Key关联的不是Sorted Set类型，相关的错误信息将被返回。
     * 
     * @param string $key
     * @param string|int $increment
     * @param string $member
     * @return string 以字符串形式表示的新分数。
     */
    public function zincrby($key, $increment, $member) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zincrby($key, $increment, $member);
    }

    /**
     * 获得排名在某个范围的元素列表 
     * 
     * 该命令返回顺序在参数start和stop指定范围内的成员，这里start和stop参数都是0-based，即0表示第一个成员，-1表示最后一个成员。
     * 如果start大于该Sorted Set中的最大索引值，或start > stop，此时一个空集合将被返回。
     * 如果stop大于最大索引值，该命令将返回从start到集合的最后一个成员。
     * 如果命令中带有可选参数WITHSCORES选项，该命令在返回的结果中将包含每个成员的分数值，如value1,score1,value2,score2...。　　
     * 
     * @param string $key
     * @param int $start        开始索引    
     * @param int $stop         结束索引
     * @param bool $WITHSCORES  结果是否包括分数
     * @return array 返回索引在start和stop之间的成员列表。默认返回[member1,member2,member3]，$WITHSCORES = true时 返回 [member1,score1,member2,score2...]
     */
    public function zrange($key, $start, $stop, $WITHSCORES = false) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zrange($key, $start, $stop, $WITHSCORES ? 'WITHSCORES' : null);
    }

    /**
     * 获得排名在某个范围的元素列表（元素分数从大到小排序） 
     * 
     * 该命令的功能和ZRANGE基本相同，唯一的差别在于该命令是通过反向排序获取指定位置的成员，即从高到低的顺序。如果成员具有相同的分数，则按降序字典顺序排序。
     * 
     * @param string $key
     * @param int $start        开始索引    
     * @param int $stop         结束索引
     * @param bool $WITHSCORES  结果是否包括分数
     * @return array 返回索引在start和stop之间的成员列表。
     */
    public function zrevrange($key, $start, $stop, $WITHSCORES = false) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zrevrange($key, $start, $stop, $WITHSCORES ? 'WITHSCORES' : null);
    }

    /**
     * 获得指定分数范围的元素
     * 
     * 该命令将返回分数在min和max之间的所有成员，即满足表达式min <= score <= max的成员，
     * 其中返回的成员是按照其分数从低到高的顺序返回，如果成员具有相同的分数，则按成员的字典顺序返回。
     * 可选参数LIMIT用于限制返回成员的数量范围。
     * 可选参数offset表示从符合条件的第offset个成员开始返回，同时返回count个成员。
     * 可选参数WITHSCORES的含义参照ZRANGE中该选项的说明。
     * 最后需要说明的是参数中min和max的规则可参照命令ZCOUNT。
     * 
     * @param string $key
     * @param string $min       最小分数 默认包括 $min ，不包括："($min"
     * @param string $max       最大分数 默认包括 $max ，不包括："($max"
     * @param bool $WITHSCORES  结果是否包括分数
     * @param int $LIMIT        限制返回成员的数量范围
     * @param int $offset       offset表示从符合条件的第offset个成员开始返回
     * @param int $count        返回count个成员
     * @return string|array     返回分数在指定范围内的成员列表。
     */
    public function zrangebyscore($key, $min, $max, $WITHSCORES = false, $LIMIT = false, $offset = null, $count = null) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zrangebyscore($key, $min, $max, $WITHSCORES ? 'WITHSCORES' : null, $LIMIT ? "LIMIT" : null, $offset, $count);
    }

    /**
     * 获得指定分数范围的元素（元素分数从大到小排序）
     * 
     * 该命令除了排序方式是基于从高到低的分数排序之外，其它功能和参数含义均与ZRANGEBYSCORE相同。
     * 【注意】需要注意的是该命令中的min和max参数的顺序和ZRANGEBYSCORE命令是相反的。
     * 
     * @param string $key
     * @param string $max       最大分数 默认包括 $max ，不包括："($max"
     * @param string $min       最小分数 默认包括 $min ，不包括："($min"
     * @param bool $WITHSCORES  结果是否包括分数
     * @param int $LIMIT        限制返回成员的数量范围
     * @param int $offset       offset表示从符合条件的第offset个成员开始返回
     * @param int $count        返回count个成员
     * @return string|array     返回分数在指定范围内的成员列表。
     */
    public function zrevrangebyscore($key, $max, $min, $WITHSCORES = false, $LIMIT = false, $offset = null, $count = null) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zrevrangebyscore($key, $max, $min, $WITHSCORES ? 'WITHSCORES' : null, $LIMIT ? "LIMIT" : null, $offset, $count);
    }

    /**
     * 获得元素的排名
     * 
     * Sorted Set中的成员都是按照分数从低到高的顺序存储，该命令将返回参数中指定成员的位置值，其中0表示第一个成员，它是Sorted Set中分数最低的成员。
     * 
     * @param string $key
     * @param string $member
     * @return int 如果该成员存在，则返回它的位置索引值。否则返回nil。
     */
    public function zrank($key, $member) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zrank($key, $member);
    }

    /**
     * 获得元素的排名（元素分数从大到小排序） 
     * 
     * 该命令的功能和ZRANK基本相同，唯一的差别在于该命令获取的索引是从高到低排序后的位置，同样0表示第一个元素，即分数最高的成员。  
     * 
     * @param type $key
     * @param type $member
     * @return int 如果该成员存在，则返回它的位置索引值。否则返回nil。
     */
    public function zrevrank($key, $member) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zrevrank($key, $member);
    }

    /**
     * 删除一个或多个元素
     * 
     * 该命令将移除参数中指定的成员，其中不存在的成员将被忽略。如果与该Key关联的Value不是Sorted Set，相应的错误信息将被返回。 
     * 
     * @param string $key
     * @param string|array $member  
     * @return int 实际被删除的成员数量。
     */
    public function zrem($key, $member) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zrem($key, ...(array) $member);
    }

    /**
     * 按照排名范围删除元素
     * 
     * 删除索引位置位于start和stop之间的成员，start和stop都是0-based，即0表示分数最低的成员，-1表示最后一个成员，即分数最高的成员。
     * 
     * @param string $key
     * @param int $start        开始索引，即0表示分数最低的成员
     * @param int $stop         结束索引，-1表示最后一个成员
     * @return int              被删除的成员数量
     */
    public function zremrangebyrank($key, $start, $stop) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zremrangebyrank($key, $start, $stop);
    }

    /**
     * 按照分数范围删除元素
     * 
     * 删除分数在min和max之间的所有成员，即满足表达式min <= score <= max的所有成员。对于min和max参数，可以采用开区间的方式表示，具体规则参照ZCOUNT。 
     * 
     * @param string $key
     * @param string $min       最小分数 默认包括 $min ，不包括："($min"
     * @param string $max       最大分数 默认包括 $max ，不包括："($max"
     * @return int              被删除的成员数量
     */
    public function zremrangebyscore($key, $min, $max) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->zremrangebyscore($key, $min, $max);
    }
}
