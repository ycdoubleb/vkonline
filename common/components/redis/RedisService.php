<?php

namespace common\components\redis;

use Yii;
use yii\redis\Connection;

/**
 * @property Redis $r Redis
 */
class RedisService
{     
    /**
     *
     * @var Redis 
     */
    private static $_redis; 
    
    /**
     * 
     * @return Redis;
     */
    public static function getRedis(){
        if(self::$_redis == null){
            self::$_redis = new Redis();
        }
        return self::$_redis;
    }
    
    /**
     * 生成16位随机码
     * @param array $codes              开始字符数组
     * @param interger $start_year      开始年份
     * @return string
     */
    public static function getRandomSN($codes = null, $start_year = 2019) {
        $yCode = $codes ? $codes : array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M','N');
        $key_sn = $yCode[intval(date('Y')) - $start_year] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5);
        $key = "RandomSN:$key_sn";
        $num = 1;
        $r = self::getRedis();
        //一秒内包括 99999 个自增ID
        if($r->exists($key)){
            $num = $r->incr($key);
        }else{
            //不存先创建一个，并设置1分钟过期
            $r->setex($key, 60, 1);
        }
        $orderSn = $key_sn .sprintf('%05d', $num). sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
}