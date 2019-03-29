<?php

namespace backend\modules\rediscache_admin\controllers;

use common\components\redis\RedisService;
use common\models\api\ApiResponse;
use Yii;
use yii\web\Controller;

/**
 * Default controller for the `rediscache_admin` module
 */
class AclController extends Controller {

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex() {
        return $this->render('index');
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
    public function actionSearchKey($key = "*") {
        Yii::$app->getResponse()->format = 'json';
        return new ApiResponse(ApiResponse::CODE_COMMON_OK, null, [
            'keys' => RedisService::getRedis()->keys((String) $key)
        ]);
    }

    /**
     * 获取
     * @param string $key
     */
    public function actionGetValue($key) {
        Yii::$app->getResponse()->format = 'json';
        if (RedisService::getRedis()->exists($key)) {
            return new ApiResponse(ApiResponse::CODE_COMMON_OK, null, [
                'key' => $key,
                'type' => RedisService::getRedis()->type($key),
                'ttl' => RedisService::getRedis()->ttl($key),
                'values' => $this->getValue($key),
            ]);
        } else {
            return new ApiResponse(ApiResponse::CODE_COMMON_NOT_FOUND,null,['param' => 'Key']);
        }
    }
    
    /**
     * 获取key值
     * @param string $key
     */
    private function getValue($key){
        $type =  RedisService::getRedis()->type($key);
        switch ($type){
            case 'string':
                return RedisService::getRedis()->get($key);
            case 'list':
                return RedisService::getRedis()->lrange($key);
            case 'set':
                return RedisService::getRedis()->smembers($key);
            case 'zset':
                return RedisService::getRedis()->zrange($key, 0, -1, true);
            case 'hash':
                return RedisService::getRedis()->hmget($key, RedisService::getRedis()->hkeys($key));
        }
        return null;
    }

}
