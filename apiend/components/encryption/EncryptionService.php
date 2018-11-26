<?php

namespace apiend\components\encryption;

use Yii;

/**
 * 加密服务
 *
 * @author Administrator
 */
class EncryptionService {

    /**
     * 加密数据
     * @param array $data
     * @param bool $use_json_encode 是否使用json_encode转换成json字符串
     * @return Base64(aes128) 加密后字符
     */
    public static function encrypt($data, $use_json_encode = true) {
        if (empty($data))
            return '';
        $secret_key = Yii::$app->params['encryption']['secret_key'];
        $method = Yii::$app->params['encryption']['method'];
        $options = Yii::$app->params['encryption']['options'];
        if($use_json_encode){
            $data = json_encode($data);
        }
        $aes128 = openssl_encrypt($data, $method, $secret_key, $options);
        $sec = bin2hex($aes128);
        //return bin2hex($aes128);
        return $sec ? $sec : '';
    }

    /**
     * 解密数据
     * @param string $data   
     * @param bool $use_json_decode 是否使用json_decode转换成json对象
     * @return array 解密后 array
     */
    public static function decrypt($data, $use_json_decode = true) {
        if (empty($data))
            return '';
        $secret_key = Yii::$app->params['encryption']['secret_key'];
        $method = Yii::$app->params['encryption']['method'];
        $options = Yii::$app->params['encryption']['options'];

        $aes128 = hex2bin($data);
        $dec = openssl_decrypt($aes128, $method, $secret_key, $options);

        return $dec ? ($use_json_decode ? json_decode($dec,true) : $dec) : '';
    }

}
