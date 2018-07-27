<?php

namespace common\utils;

use Yii;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * www.studying8.com 加密通信
 *
 * @author Administrator
 */
class SecurityUtil {

    /**
     * 数据加密码
     * @param    string|array     $data     [用户ID]
     * @return   string                     [生成的数据]
     */
    static public function encryption($data) {
        #获取配置文件中Token验证参数
        $auth = yii::$app->params['secret_auth'];
        #获取当前时间[目的:设置时间超时机制]
        $nowTime = time();

        #两次md5加密保证密钥安全性
        $secret = md5(md5($auth['secret'] . $nowTime));
        #设置加密数据[目的:拼接当前时间 & 传递参数]
        $source_data = [
            'secret' => $secret,
            'time' => $nowTime,
            'data' => $data,
        ];

        #设置加密密码[目的:拼接用户ID,设置动态Key值]
        $secret = $auth['key'];

        #Yii加密算法生成Toekn(参数1:加密数据 参数2:自定义密码)
        $encryption_str = Yii::$app->getSecurity()->encryptByPassword(json_encode($source_data), $secret);
        #由于生成的token乱码，我们可以base64加密,以便后续查看
        $encryption_str = base64_encode($encryption_str);
        return $encryption_str;
    }

    /**
     * 解密
     * @param    string     $encryption_str    [加密的数据]
     * @return   Json                          [验证结果]
     */
    static public function decryption($encryption_str) {
        #获取配置文件中Token验证参数
        $auth = yii::$app->params['secret_auth'];
        #获取Token生成时的加密密码
        $secret = $auth['key'];
        #base64解码token
        $encryption_str = base64_decode($encryption_str);

        #Yii解密算法获取加密数据(参数1:Token 参数2:Token生成时设置的密码)
        #失败返回false,成功返回Token
        try {
            $data = json_decode(Yii::$app->getSecurity()->decryptByPassword($encryption_str, $secret),true);
        } catch (Exception $ex) {
            return null;
        }

        if (!$data) {
            return null;
        } else {
            #检测时间超时机制
            if (time() - $data['time'] > $auth['timeout']) {
                return null;
            } else {
                #检测加密密钥
                $secret = md5(md5($auth['secret'] . $data['time']));
                if ($secret == $data['secret']) {
                    return $data['data'];
                } else {
                    return null;
                }
            }
        }
        return null;
    }

}
