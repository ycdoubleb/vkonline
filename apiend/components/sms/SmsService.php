<?php

namespace apiend\components\sms;

use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Component;

/**
 * 短信服务
 *
 * @author Administrator
 */
class SmsService extends Component {

    //1：成功，2：失败，3：参数错误，4:应用ID与模板ID不匹配,5:服务器内部错误,6:其他
    const SMS_ERROR = [
        1 => '成功',
        2 => '失败',
        3 => '参数错误',
        4 => '应用ID与模板ID不匹配',
        5 => '服务器内部错误',
        6 => '其他',
    ];
    /**
     * 发送验证码
     * @param integer $phone    电话号码
     * @param string $sms_template_id   短信模板
     * @param integer $timeout 失效时间
     * @return array
     */
    public static function sendCode($phone, $sms_template_id, $timeout = 30*60) {
        $sendYunSmsConfig = Yii::$app->params['sendYunSms'];         //发送验证码配置
        $SMS_APP_ID = $sendYunSmsConfig['SMS_APP_ID'];                          //应用ID
        
        $str='0123456789876543210';  
        $randStr = str_shuffle($str);           //打乱字符串  
        $code = substr($randStr, 0, 4);         //生成验证码【substr(string,start,length);返回字符串的一部分】
        $code_key = md5("SMS_". time(). rand(0, 99999));
                
        //把生成的验证码和手机保存到redis数据库里，设置30分钟超时
        Yii::$app->redis->setex($code_key, $timeout, md5("$phone-$code"));
        
        //传递的参数【必须是以下xml格式】
        $xmlDatas = '<?xml version="1.0" encoding="UTF-8"?>' .
                '<tranceData>' .
                    "<MOBILE><![CDATA[$phone]]></MOBILE>" .
                    "<SMS_TEMPLATE_ID><![CDATA[$sms_template_id]]></SMS_TEMPLATE_ID>" .
                    "<SMS_APP_ID><![CDATA[$SMS_APP_ID]]></SMS_APP_ID>" .
                    '<PARAMS>' .
                        "<![CDATA[$code]]>" .
                    '</PARAMS>' .
                '</tranceData>';

        $url = 'http://eesms.gzedu.com/sms/sendYunSms.do';  //发送短信的请求地址
        $curl = new Curl();
        $response = $curl
                ->setOption(CURLOPT_HTTPHEADER, Array("Content-Type:text/xml; charset=utf-8"))
                ->setOption(CURLOPT_POSTFIELDS, $xmlDatas)->post($url); //提交发送
        //转换为simplexml对象
        $xmlResult = simplexml_load_string($response);//XML 字符串载入对象中
        $reuslt = (integer)$xmlResult->result;
        if ($reuslt == 1) {
            return [
                'result' => true,
                'code_key' => $code_key,
            ];
        } else {
            return [
                'result' => false,
                'msg' => self::SMS_ERROR[$reuslt],
            ];
        }
    }

    /**
     * 效检验证码
     * @param integer $phone        手机号
     * @param integer $code         验证码
     * @param string $code_key      验证码关联码
     * @param bool $pass_del_code   验证通过则删除code 默认为删除
     */
    public static function verificationCode($phone, $code, $code_key , $pass_del_code = true) {
        /* 检查验证码是否正确 */
        $right_code = Yii::$app->redis->get($code_key);
        if ($right_code == null) {
            return ['result' => false, 'code' => 'CODE_SMS_INVALID', 'msg' => '验证码已失效'];
        }
        //验证码以 md5(phone-code)格式保存
        if ($right_code != md5("$phone-$code")) {
            return ['result' => false, 'code' => 'CODE_SMS_AUTH_FAILED', 'msg' => '验证码不匹对'];
        }
        //删除code
        if ($pass_del_code) {
            Yii::$app->redis->del($code_key);
        }
        return ['result' => true];
    }
    
    /**
     * 手动删除验证码
     * @param string $code_key
     */
    public static function delCode($code_key){
        Yii::$app->redis->del($code_key);
    }

}
