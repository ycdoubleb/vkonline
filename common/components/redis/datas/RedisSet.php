<?php

namespace common\components\redis\datas;

/**
 * Redis 集合类型的方法，单独放置在RedisSet类中，继承 RedisBase 方法
 * 
 * 在Redis中，我们可以将Set类型看作为没有排序的字符集合，和List类型一样，我们也可以在该类型的数据值上执行添加、删除或判断某一元素是否存在等操作。
 * 需要说明的是，这些操作的时间复杂度为O(1)，即常量时间内完成次操作。
 * Set可包含的最大元素数量是4294967295。
 * 和List类型不同的是，Set集合中不允许出现重复的元素，这一点和C++标准库中的set容器是完全相同的。
 * 换句话说，如果多次添加相同元素，Set中将仅保留该元素的一份拷贝。
 * 和List类型相比，Set类型在功能上还存在着一个非常重要的特性，即在服务器端完成多个Sets之间的聚合计算操作，如unions、intersections和differences。
 * 由于这些操作均在服务端完成，因此效率极高，而且也节省了大量的网络IO开销。
 *
 * @author Administrator
 */
class RedisSet extends RedisBase {

    /**
     * # SADD key member1 [member2]
     *
     * 增加元素，向集合添加一个或多个成员
     * 
     * 时间复杂度：O(N)时间复杂度中的N表示操作的成员数量。
     * 如果在插入的过程用，参数中有的成员在Set中已经存在，该成员将被忽略，而其它成员仍将会被正常插入。
     * 如果执行该命令之前，该Key并不存在，该命令将会创建一个新的Set，此后再将参数中的成员陆续插入。
     * 如果该Key的Value不是Set类型，该命令将返回相关的错误信息。
     *
     * @param string $key
     * @param string|array $members     
     * @return int                      本次操作实际插入的成员数量。
     */
    public function sadd($key, $members) {
        $key = self::buildKey($key);
        $redis = self::getRedis();

        return (int) $redis->sadd($key, ...(array) $members);
    }

    /**
     * 从集合中弹出一个元素
     * 
     * 随机的移除并返回Set中的某一成员。 
     * 由于Set中元素的布局不受外部控制，因此无法像List那样确定哪个元素位于Set的头部或者尾部。
     * 
     * @param string $key   
     * @return string 返回移除的成员，如果该Key并不存在，则返回nil。
     */
    public function spop($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();

        return $redis->spop($key);
    }

    /**
     * 将元素从一个集合转到另一个集合 
     * 
     * 原子性的将参数中的成员从source键移入到destination键所关联的Set中。
     * 因此在某一时刻，该成员或者出现在source中，或者出现在destination中。
     * 如果该成员在source中并不存在，该命令将不会再执行任何操作并返回0，否则，该成员将从source移入到destination。
     * 如果此时该成员已经在destination中存在，那么该命令仅是将该成员从source中移出。
     * 如果和Key关联的Value不是Set，将返回相关的错误信息。
     * 
     * @param string $srckey        源      Set   
     * @param string $dstkey        目标    Set
     * @param string $member
     * @return int 1表示正常移动，0表示source中并不包含参数成员。
     */
    public function smove($srckey, $dstkey, $member) {
        $srckey = self::buildKey($srckey);
        $dstkey = self::buildKey($dstkey);
        $redis = self::getRedis();

        return $redis->smove($srckey, $dstkey, $member);
    }

    /**
     * # SREM key member1 [member2]
     *
     * 移除集合中一个或多个成员
     * 
     * 从与Key关联的Set中删除参数中指定的成员，不存在的参数成员将被忽略，如果该Key并不存在，将视为空Set处理。
     * 
     * @param $key
     * @param $members
     * @return int      从Set中实际移除的成员数量，如果没有则返回0
     */
    public function srem($key, $members) {
        $key = self::buildKey($key);
        $redis = self::getRedis();

        return (int) $redis->srem($key, ...(array) $members);
    }

    /**
     * # SISMEMBER key member
     *
     * 判断元素是否在集合中
     * 
     * 判断参数中指定成员是否已经存在于与Key相关联的Set集合中。
     * 
     * @param string $key
     * @param string $member
     * @return int      1表示已经存在，0表示不存在，或该Key本身并不存在。
     */
    public function sismember($key, $member) {
        $key = self::buildKey($key);
        $redis = self::getRedis();

        return (int) $redis->sismember($key, $member);
    }

    /**
     * # SMEMBERS key
     *
     * 返回集合中的所有成员
     * 
     * @param string $key
     * @return array    返回Set中所有的成员。
     */
    public function smembers($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();

        return $redis->smembers($key);
    }

    /**
     * 随机获得集合中的元素
     * 
     * 和SPOP一样，随机的返回Set中的一个成员，不同的是该命令并不会删除返回的成员。
     * 还可以传递count参数来一次随机获得多个元素，根据count的正负不同，具体表现也不同。
     * 当count 为正数时，SRANDMEMBER 会随机从集合里获得count个不重复的元素。如果count的值大于集合中的元素个数，则SRANDMEMBER 会返回集合中的全部元素。
     * 当count为负数时，SRANDMEMBER 会随机从集合里获得|count|个的元素，这些元素有可能相同。
     * 
     * @param string $key
     * @param int $count 随机取的个数，正数不重复，负数重复
     * @return string|array 返回随机位置的成员，如果Key不存在则返回nil。
     */
    public function srandmember($key, $count = null) {
        $key = self::buildKey($key);
        $redis = self::getRedis();

        return $redis->srandmember($key, $count);
    }

    /**
     * # SCARD key
     *
     * 获得集合中元素个数
     * 
     * @param string $key
     * @return int          返回Set中成员的数量，如果该Key并不存在，返回0。
     */
    public function scard($key) {
        $key = self::buildKey($key);
        $redis = self::getRedis();

        return (int) $redis->scard($key);
    }

    /**
     * 集合间交集运算 
     * 
     * 该命令将返回参数中所有Keys关联的Sets中成员的交集。因此如果参数中任何一个Key关联的Set为空，或某一Key不存在，那么该命令的结果将为空集。
     * 
     * @param string $key   
     * @param ...string $keys   不定参数
     * @return array 交集结果成员的集合。
     */
    public function sinter($key, ...$keys) {
        $key = self::buildKey($key);
        foreach ($keys as &$key_i) {
            $key_i = self::buildKey($key_i);
        }
        $redis = self::getRedis();

        return $redis->sinter($key, ...$keys);
    }

    /**
     * 集合间交集运算并将结果存储
     * 
     * 命令和SINTER命令在功能上完全相同，两者之间唯一的差别是SINTER返回交集的结果成员，而该命令将交集成员存储在destination关联的Set中。
     * 如果destination键已经存在，该操作将覆盖它的成员。
     * 
     * @param string $dstkey
     * @param ...string $keys
     * @return int 返回交集成员的数量。
     */
    public function sinterstore($dstkey, ...$keys) {
        $dstkey = self::buildKey($dstkey);
        foreach ($keys as &$key_i) {
            $key_i = self::buildKey($key_i);
        }
        $redis = self::getRedis();

        return $redis->sinterstore($dstkey, ...$keys);
    }

    /**
     * 集合间并集运算
     * 
     * 该命令将返回参数中所有Keys关联的Sets中成员的并集。
     * 
     * @param string $key
     * @param ...string $keys
     * @return array 并集结果成员的集合。
     */
    public function sunion($key, ...$keys) {
        $key = self::buildKey($key);
        foreach ($keys as &$key_i) {
            $key_i = self::buildKey($key_i);
        }
        $redis = self::getRedis();

        return $redis->sunion($key, ...$keys);
    }

    /**
     * 集合间并集运算并将结果存储
     * 
     * 该命令和SUNION命令在功能上完全相同，两者之间唯一的差别是SUNION返回并集的结果成员，而该命令将并集成员存储在destination关联的Set中。
     * 如果destination键已经存在，该操作将覆盖它的成员。 
     * 
     * @param string $dstkey
     * @param ...string $keys
     * @return int 返回并集成员的数量。
     */
    public function sunionstore($dstkey, ...$keys) {
        $dstkey = self::buildKey($dstkey);
        foreach ($keys as &$key_i) {
            $key_i = self::buildKey($key_i);
        }
        $redis = self::getRedis();

        return $redis->sunionstore($dstkey, ...$keys);
    }

    /**
     * 集合间差集运算
     * 
     * 返回参数中第一个Key所关联的Set和其后所有Keys所关联的Sets中成员的差异。如果Key不存在，则视为空Set。
     * 
     * @param string $key
     * @param ...string $keys
     * @return array 差异结果成员的集合。
     */
    public function sdiff($key, ...$keys) {
        $key = self::buildKey($key);
        foreach ($keys as &$key_i) {
            $key_i = self::buildKey($key_i);
        }
        $redis = self::getRedis();

        return $redis->sdiff($key, ...$keys);
    }

    /**
     * 集合间差集运算并将结果存储
     * 
     * 该命令和SDIFF命令在功能上完全相同，两者之间唯一的差别是SDIFF返回差异的结果成员，而该命令将差异成员存储在destination关联的Set中。如果destination键已经存在，该操作将覆盖它的成员。 
     * 
     * @param string $dstkey
     * @param ...string $keys
     * @return int  返回差异成员的数量。
     */
    public function sdiffstore($dstkey, ...$keys) {
        $dstkey = self::buildKey($dstkey);
        foreach ($keys as &$key_i) {
            $key_i = self::buildKey($key_i);
        }
        $redis = self::getRedis();

        return $redis->sdiffstore($dstkey, ...$keys);
    }

}
