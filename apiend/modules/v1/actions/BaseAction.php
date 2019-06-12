<?php

namespace apiend\modules\v1\actions;

use apiend\components\encryption\EncryptionService;
use apiend\models\Response;
use common\core\ApiException;
use Yii;
use yii\base\Action;

/**
 * Description of BaseAction
 *
 * @author Administrator
 */
class BaseAction extends Action
{

    /**
     * 设置接口检验必须的参数
     * @var type 
     */
    protected $requiredParams = [];

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
    protected function beforeRun()
    {
        $this->params = array_merge($this->getBodyParams(), $this->getQueryParams());

        if ($raw = Yii::$app->request->getRawBody()) {
            try{
                $this->params = array_merge($this->params, json_decode($raw, true));
            } catch (\Exception $ex) {}
        }

        if ($secret = Yii::$app->request->getQueryParam('secret', null)) {
            $this->secretParams = EncryptionService::decrypt($secret, true);
        } else if ($secret = Yii::$app->request->getBodyParam('secret', null)) {
            $this->secretParams = EncryptionService::decrypt($secret, true);
        } else {
            //无加密情况，只有签名校对
            $this->secretParams = $this->params;
        }

        return $this->verify();
    }

    /**
     * 验证
     * @return bool
     */
    protected function verify()
    {
        $params = array_merge($this->getSecretParams(), $this->params);
        $notfounds = $this->checkRequiredParams($params, array_merge(['appkey', 'sign', 'timestamp'], $this->requiredParams));
        if (count($notfounds) > 0) {
            throw new ApiException(new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => implode(',', $notfounds)]));
        }

        //检查时效
        $timestamp = $this->getParam("timestamp");
        if (time() * 1000 - $timestamp > 60 * 1000) {
            throw new ApiException(new Response(Response::CODE_COMMON_TIMEOUT, null, ["server_time" => time() * 1000]));
        }
        if (!$this->getParam('secret')) {
            //没加密情况
            if (!$this->verifySignNoSecret($this->getParam('sign'))) {
                throw new ApiException(new Response(Response::CODE_COMMON_VERIFY_SIGN_FAIL));
            }
        } else if (!$this->verifySign($this->getParam('appkey'), $timestamp, $this->getParam('sign'))) {
            //加密情况
            throw new ApiException(new Response(Response::CODE_COMMON_VERIFY_SIGN_FAIL));
        }

        return true;
    }

    /**
     * 校验签名 
     * @param string $appkey    应用名称
     * @param int $timestamp    时间戳
     * @param string $sign      签名
     */
    protected function verifySign($appkey, $timestamp, $sign)
    {
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
    protected function verifySignNoSecret($sign)
    {
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
    protected function checkRequiredParams($arr, $params)
    {
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
     * @param Array $defaultValue      参数为空时返回的值
     */
    public function getParam($name, $defaultValue = null)
    {
        return isset($this->params[$name]) ? $this->params[$name] : $defaultValue;
    }

    /**
     * 获取(Query/Body)参数
     * @param String $name              参数名
     * @param Array $defaultValue      参数为空时返回的值
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * 获取 Query 传参
     * @param String $name              参数名
     * @param Array $defaultValue      参数为空时返回的值
     */
    public function getQueryParam($name, $defaultValue = null)
    {
        return Yii::$app->request->getQueryParam($name, $defaultValue);
    }

    /**
     * 获取 Query 所有传参
     * @return array 
     */
    public function getQueryParams()
    {
        return Yii::$app->request->getQueryParams();
    }

    /**
     * 获取 Body 传参
     * @param String $name              参数名
     * @param Array $defaultValue      参数为空时返回的值
     */
    public function getBodyParam($name, $defaultValue = null)
    {
        return Yii::$app->request->getBodyParam($name, $defaultValue);
    }

    /**
     * 获取 Body 所有传参
     * @return array 
     */
    public function getBodyParams()
    {
        return Yii::$app->request->getBodyParams();
    }

    /**
     * 获取加密(Secret)参数
     * @param String $name              参数名
     * @param Array $defaultValue      参数为空时返回的值
     */
    public function getSecretParam($name, $defaultValue = null)
    {
        return isset($this->secretParams[$name]) ? $this->secretParams[$name] : $defaultValue;
    }

    /**
     * 获取报有加密(Secret)参数
     * @return array 
     */
    public function getSecretParams()
    {
        return $this->secretParams;
    }

}
