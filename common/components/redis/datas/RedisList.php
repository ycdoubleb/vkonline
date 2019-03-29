<?php

namespace common\components\redis\datas;

/**
 * Redis List类型的方法，单独放置在RedisList类中，继承 RedisBase 方法
 *
 * @author Administrator
 */
class RedisList extends RedisBase {

    /**
     * 向列表右边添加元素
     * 
     * 在指定Key所关联的List Value的尾部插入参数中给出的所有Values。
     * 如果该Key不存在，该命令将在插入之前创建一个与该Key关联的空链表，之后再将数据从链表的尾部插入。
     * 如果该键的Value不是链表类型，该命令将返回相关的错误信息。 
     * 
     * @param string $key
     * @param array $value
     * @return int 插入后链表中元素的数量。
     */
    public function rpush($key, $values) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->rpush($key, ...(array) $values);
    }

    /**
     * 向列表左边添加元素
     * 
     * 在指定Key所关联的List Value的头部插入参数中给出的所有Values。
     * 如果该Key不存在，该命令将在插入之前创建一个与该Key关联的空链表，之后再将数据从链表的头部插入。
     * 如果该键的Value不是链表类型，该命令将返回相关的错误信息。
     * 
     * @param string $key
     * @param type $value
     * @return int  插入后链表中元素的数量。
     */
    public function lpush($key, $values) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->lpush($key, ...(array) $values);
    }

    /**
     * 获取列表中元素的个数
     * 
     * 返回指定Key关联的链表中元素的数量，如果该Key不存在，则返回0。如果与该Key关联的Value的类型不是链表，则返回相关的错误信息。
     * 
     * @param string $key
     * @return int  链表中元素的数量。
     */
    public function llen($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->llen($key);
    }

    /**
     * 获得列表片段
     * 
     * 时间复杂度：O(S+N)
     * 
     * 时间复杂度中的S为start参数表示的偏移量，N表示元素的数量。
     * 该命令的参数start和end都是0-based。
     * 即0表示链表头部(leftmost)的第一个元素。
     * 其中start的值也可以为负值，-1将表示链表中的最后一个元素，即尾部元素，-2表示倒数第二个并以此类推。
     * 该命令在获取元素时，start和end位置上的元素也会被取出。
     * 如果start的值大于链表中元素的数量，空链表将会被返回。
     * 如果end的值大于元素的数量，该命令则获取从start(包括start)开始，链表中剩余的所有元素。
     * 注：Redis的列表起始索引为0。显然，LRANGE numbers 0 -1 可以获取列表中的所有元素。
     * 
     * @param string $key
     * @param int $start        开始字符索引，默认为第一个字符，可用负数表示倒数每几位 如 -2 表示结束为倒数第二个字符
     * @param int $end          结束字符索引，默认为最后一个字符，可用负数表示倒数每几位 如 -2 表示结束为倒数第二个字符
     * @return int              返回指定范围内元素的列表。
     */
    public function lrange($key, $start = 0, $end = -1) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->lrange($key, $start, $end);
    }

    /**
     * 只保留列表指定片段
     * 
     * 时间复杂度：O(N) N表示被删除的元素数量。
     * 
     * 该命令将仅保留指定范围内的元素，从而保证链接中的元素数量相对恒定。
     * start和stop参数都是0-based，0表示头部元素。
     * 和其他命令一样，start和stop也可以为负值，-1表示尾部元素。
     * 如果start大于链表的尾部，或start大于stop，该命令不报错，而是返回一个空的链表，与此同时该Key也将被删除。如果stop大于元素的数量，则保留从start开始剩余的所有元素。 
     * 
     * @param string $key
     * @param int $start
     * @param int $end
     * @return type
     */
    public function ltrim($key, $start, $end) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->ltrim($key, $start, $end);
    }

    /**
     * 获取指定索引的元素值
     * 
     * 该命令将返回链表中指定位置(index)的元素，index是0-based，表示头部元素，如果index为-1，表示尾部元素。
     * 如果与该Key关联的不是链表，该命令将返回相关的错误信息。 
     * 
     * @param string $key
     * @param int $index
     * @return string       返回请求的元素，如果index超出范围，则返回nil。
     */
    public function lindex($key, $index) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->lindex($key, $index);
    }

    /**
     * 给名称为key的list中index位置的元素赋值
     * 时间复杂度：O(N)
     * 
     * 时间复杂度中N表示链表中元素的数量。
     * 但是设定头部或尾部的元素时，其时间复杂度为O(1)。
     * 设定链表中指定位置的值为新值，其中0表示第一个元素，即头部元素，-1表示尾部元素。
     * 如果索引值Index超出了链表中元素的数量范围，该命令将返回相关的错误信息。
     * 
     * @param type $key
     * @param type $index
     * @param type $value
     * @return type
     */
    public function lset($key, $index, $value) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->lset($key, $index, $value);
    }

    /**
     * 删除列表中指定的值。在指定Key关联的链表中，删除前count个值等于value的元素。
     * 
     * 时间复杂度：O(N)  时间复杂度中N表示链表中元素的数量。
     * 
     * 如果count大于0，从头向尾遍历并删除，
     * 如果count小于0，则从尾向头遍历并删除。
     * 如果count等于0，则删除链表中所有等于value的元素。
     * 如果指定的Key不存在，则直接返回0。
     * 
     * @param string $key
     * @param int $count
     * @param string $value
     * @return type
     */
    public function lrem($key, $count, $value) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->lrem($key, $count, $value);
    }

    /**
     * 从列表左边弹出元素
     * 
     * 时间复杂度：O(1)
     * 返回并弹出指定Key关联的链表中的第一个元素，即头部元素。如果该Key不存在，返回nil。
     * LPOP命令执行两步操作：第一步是将列表左边的元素从列表中移除，第二步是返回被移除的元素值。
     * 
     * @param string $key
     * @return string           链表头部的元素。
     */
    public function lpop($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->lpop($key);
    }

    /**
     * 从列表右边弹出元素
     * 
     * 时间复杂度：O(1)
     * 
     * 返回并弹出指定Key关联的链表中的最后一个元素，即尾部元素。如果该Key不存在，返回nil。
     * 
     * @param string $key
     * @return string           链表尾部的元素。
     */
    public function rpop($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();
        return $redis->rpop($key);
    }

    /**
     * 将元素从一个列表转到另一个列表
     * 
     * 时间复杂度：O(1)
     * 
     * 原子性的从与source键关联的链表尾部弹出一个元素，同时再将弹出的元素插入到与destination键关联的链表的头部。
     * 如果source键不存在，该命令将返回nil，同时不再做任何其它的操作了。
     * 如果source和destination是同一个键，则相当于原子性的将其关联链表中的尾部元素移到该链表的头部。 
     * 
     * RPOPLPUSH是个很有意思的命令，从名字就可以看出它的功能：先执行RPOP命令再执行LPUSH命令。
     * RPOPLPUSH命令会先从source 列表类型键的右边弹出一个元素，然后将其加入到destination 列表类型键的左边，并返回这个元素的值，整个过程是原子的。
     * 
     * @param string $srckey
     * @param string $dstkey
     * @return string           返回弹出和插入的元素。
     */
    public function rpoplpush($srckey, $dstkey) {
        $srckey = self::buildKey($srckey);
        $dstkey = self::buildKey($dstkey);
        $redis = self::getRedis();
        return $redis->rpoplpush($srckey, $dstkey);
    }

    //blpop(key1, key2,… key N, timeout)：lpop命令的block版本。
    //brpop(key1, key2,… key N, timeout)：rpop的block版本。
}
