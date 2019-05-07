<?php

namespace apiend\modules\v1\actions;

use apiend\components\encryption\EncryptionService;
use apiend\models\Response;
use Yii;
use yii\base\Action;
use yii\base\Object;

/**
 * Description of BaseAction
 *
 * @author Administrator
 */
class BaseAction extends Action {

    /**
     * 合并了 QueryParam 及 BodyParam 参数
     * */
    protected $params = [];

    /**
     *
     * @var type 
     */
    protected $secretParams = [];
    
    /**
     * 验证错误
     * @var type 
     */
    protected $verifyError = null;

    //--------------------------------------------------------------------------------------------
    //
    //  protected method
    //
    //--------------------------------------------------------------------------------------------
    /**
     * 执行前先解密
     * @return boolean
     */
    protected function beforeRun() {
        $this->params = array_merge($this->getBodyParams(), $this->getQueryParams());
        
        if($raw = Yii::$app->request->getRawBody()){
            $this->params = array_merge($this->params, json_decode($raw,true));
        }
        
        if ($secret = Yii::$app->request->getQueryParam('secret', null)) {
            $this->secretParams = EncryptionService::decrypt($secret, true);
        } else if ($secret = Yii::$app->request->getBodyParam('secret', null)) {
            $this->secretParams = EncryptionService::decrypt($secret, true);
        }else{
            //无加密情况，只有签名校对
            $this->secretParams = $this->params;
        }
        
        return true;
    }
    
    /**
     * 验证
     * @return bool
     */
    protected function verify(){
        $notfounds = $this->checkRequiredParams($this->params, ['appkey', 'sign', 'timestamp']);
        if (count($notfounds) > 0) {
            $this->verifyError = new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => implode(',', $notfounds)]);
             return false;
        }
        
        //检查时效
        $timestamp = $this->getParam("timestamp");
        if (time() * 1000 - $timestamp > 60 * 1000) {
            $this->verifyError = new Response(Response::CODE_COMMON_TIMEOUT, null, ["server_time" => time() * 1000]);
            return false;
        }
        if(!$this->getParam('secret')){
            //没加密情况
            if(!$this->verifySignNoSecret($this->getParam('sign'))){
                $this->verifyError = new Response(Response::CODE_COMMON_VERIFY_SIGN_FAIL);
                return false;
            }
        }else if(!$this->verifySign($this->getParam('appkey'), $timestamp, $this->getParam('sign'))){
            //加密情况
            $this->verifyError = new Response(Response::CODE_COMMON_VERIFY_SIGN_FAIL);
             return false;
        }
        
        return true;
    }

    /**
     * 校验签名 
     * @param string $appkey    应用名称
     * @param int $timestamp    时间戳
     * @param string $sign      签名
     */
    protected function verifySign($appkey, $timestamp, $sign) {
        $secret_arr = [];
        foreach ($this->secretParams as $key => $value) {
            $secret_arr [] = "$key$value";
        }
        sort($secret_arr);
        $secret_sort_str = implode("", $secret_arr);
        return strtoupper(md5("{$appkey}{$secret_sort_str}{$timestamp}{$appkey}")) == $sign;
    }
    
    /**
     * 校验签名 
     * @param string $sign
     * @return boolean
     */
    protected function verifySignNoSecret($sign){
        $secret_arr = [];
        foreach ($this->secretParams as $key => $value) {
            if ($key != 'sign') {
                $secret_arr [] = "$key$value";
            }
        }
        sort($secret_arr);
        $secret_sort_str = implode("", $secret_arr);
        return strtoupper(md5("wskeee{$secret_sort_str}wskeee")) == $sign;
    }

    /**
     * 检查指定数组内是否包括指定参数   
     * @param array $arr                指定检查的数组
     * @param array|string $params      指定必须的参数
     * 
     * @return array 发现未包括的参数
     */
    protected function checkRequiredParams($arr, $params) {
        $notfounds = [];
        if (is_string($params)) {
            $params = [$params];
        }
        foreach ($params as $param) {
            if (!isset($arr[$param]) || $arr[$param] == "") {
                $notfounds[] = $param;
            }
        }
        return $notfounds;
    }

    //--------------------------------------------------------------------------------------------
    //
    //  get & set
    //
    //--------------------------------------------------------------------------------------------

    /**
     * 获取(Query/Body)参数
     * @param String $name              参数名
     * @param Object $defaultValue      参数为空时返回的值
     */
    public function getParam($name, $defaultValue = null) {
        return isset($this->params[$name]) ? $this->params[$name] : $defaultValue;
    }

    /**
     * 获取(Query/Body)参数
     * @param String $name              参数名
     * @param Object $defaultValue      参数为空时返回的值
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * 获取 Query 传参
     * @param String $name              参数名
     * @param Object $defaultValue      参数为空时返回的值
     */
    public function getQueryParam($name, $defaultValue = null) {
        return Yii::$app->request->getQueryParam($name, $defaultValue);
    }

    /**
     * 获取 Query 所有传参
     * @return array 
     */
    public function getQueryParams() {
        return Yii::$app->request->getQueryParams();
    }

    /**
     * 获取 Body 传参
     * @param String $name              参数名
     * @param Object $defaultValue      参数为空时返回的值
     */
    public function getBodyParam($name, $defaultValue = null) {
        return Yii::$app->request->getBodyParam($name, $defaultValue);
    }

    /**
     * 获取 Body 所有传参
     * @return array 
     */
    public function getBodyParams() {
        return Yii::$app->request->getBodyParams();
    }

    /**
     * 获取加密(Secret)参数
     * @param String $name              参数名
     * @param Object $defaultValue      参数为空时返回的值
     */
    public function getSecretParam($name, $defaultValue = null) {
        return isset($this->secretParams[$name]) ? $this->secretParams[$name] : $defaultValue;
    }

    /**
     * 获取报有加密(Secret)参数
     * @return array 
     */
    public function getSecretParams() {
        return $this->secretParams;
    }

}
