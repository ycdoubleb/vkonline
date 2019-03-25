<?php

namespace common\utils;

use apiend\components\encryption\EncryptionService;
use linslin\yii2\curl\Curl;
use Yii;

/**
 * ApiService
 * 接口服务
 *
 * @author Administrator
 */
class ApiService {

    /**
     * [
      'secret_key' => 'api.mediacloud',        //密码
      'method' => 'aes-128-ecb',              //加密方法
      'options' => OPENSSL_RAW_DATA,          //选项
      ],
     * @var type 
     */
    private static $encryption_config = [];

    /**
     * 组装参数
     * @param array $params
     */
    private static function _createParams($params) {
        $timestamp = time() * 1000;
        $appkey = 'mediacloud';
        $secret = EncryptionService::encrypt($params, true, self::$encryption_config);
        /**
         * 
         * 签名算法过程：
         * 1.对除签名外的所有请求参数按key做的升序排列,value无需编码。（假设当前时间的时间戳是12345678）
         * 例如：有c=3,b=2,a=1 三个参，另加上时间戳后， 按key排序后为：a=1，b=2，c=3，timestamp=12345678。
         * 2 把参数名和参数值连接成字符串，得到拼装字符：a1b2c3_timestamp12345678
         * 3 用申请到的appkey 连接到接拼装字符串头部和尾部，然后进行32位MD5加密，最后将到得MD5加密摘要转化成大写。
         * 示例：假设appkey=test，md5(testa1b2c3timestamp12345678test)，取得MD5摘要值 C5F3EB5D7DC2748AED89E90AF00081E6 。 
         * */
        $data_arr = [];
        foreach ($params as $key => $param) {
            $data_arr [] = "$key$param";
        }
        sort($data_arr);
        $data_sort_str = implode("", $data_arr);
        $sign = strtoupper(md5("{$appkey}{$data_sort_str}{$timestamp}{$appkey}"));

        return [
            'secret' => $secret,
            'timestamp' => $timestamp,
            'appkey' => $appkey,
            'sign' => $sign,
        ];
    }

    /**
     * 初始加密配置
     * @param array $encryption_config  
     */
    public static function init($encryption_config) {
        self::$encryption_config = $encryption_config;
    }

    /**
     * 调用 GET 请求
     * @param string $url       接口地址
     * @param array $params     参数
     * @param bool $raw         保留原格式，设置false会自动转换为json
     */
    public static function get($url, $params = [], $raw = false) {
        //调用API
        $curl = new Curl();
        $curl->setGetParams(self::_createParams($params));
        return $curl->get($url, $raw);
    }

    /**
     * 调用 POST 请求
     * @param string $url           接口地址
     * @param array $params         参数
     * @param string|array|raw $body    源数据
     * @param bool $raw         保留原格式，设置false会自动转换为json
     */
    public static function post($url, $params = [], $body = null, $raw = false) {
        //调用API
        $curl = new Curl();
        $curl->setPostParams(self::_createParams($params));
        return $curl->post($url, $raw);
    }

}
