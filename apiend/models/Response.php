<?php
namespace apiend\models;
use common\models\api\ApiResponse;

/**
 * 包括所有 API 错误返馈码及错误描述
 *
 * @author Administrator
 */
class Response extends ApiResponse{
    
    //--------------------------------------------------------------------------------------------------------------
    //
    // 所有 CODE 值范围 = 10000-99999 其中 10000-10099 为保留范围，请勿使用
    // 
    //--------------------------------------------------------------------------------------------------------------
    //
    //--------------------------------------------------------------------------------------------------------------
    //
    // 账号 CODE 值范围 = 10100-10199
    // 
    //--------------------------------------------------------------------------------------------------------------
    /** 登录验证失败 */
    const CODE_USER_AUTH_FAILED = '10100';
    /** 该用户名已经注册 */
    const CODE_USER_USERNAME_HAS_REGISTERED = '10101';
    /** 该手机号已经注册 */
    const CODE_USER_PHONE_HAS_REGISTERED = '10102';
    /** 注册失败 */
    const CODE_USER_REGISTER_FAILED = '10103';
    /** 第三方账号已存在 */
    const CODE_USER_AUTH_ACCOUNT_EXISTS = '10104';
    
    
    //--------------------------------------------------------------------------------------------------------------
    //
    // SMS CODE 值范围 = 10200-10299
    // 
    //--------------------------------------------------------------------------------------------------------------
    /** 验证码不匹对 */
    const CODE_SMS_AUTH_FAILED = '10200';
    /** 验证码已失效 */
    const CODE_SMS_INVALID = '10201';
    /** 发送失败 */
    const CODE_SMS_SEND_FAILED = '10202';
    /** 找不到对应模板 */
    const CODE_SMS_TEMPLATE_NOT_FOUND = '10203';
    
    //--------------------------------------------------------------------------------------------------------------
    //
    // 对应描述
    // 
    //--------------------------------------------------------------------------------------------------------------
    /**
     * 返回 code 与 反馈修改的对应关系
     * 使用时由子类合并使用，注意：请使用  + 号合并数组，保留原来键值
     */
    public function getCodeMap(){
        return parent::getCodeMap() + [
            /* USER */
            self::CODE_USER_AUTH_FAILED => '登录验证失败',
            self::CODE_USER_USERNAME_HAS_REGISTERED => '该用户名已经注册',
            self::CODE_USER_PHONE_HAS_REGISTERED => '该手机号已经注册',
            self::CODE_USER_REGISTER_FAILED => '注册失败',
            self::CODE_USER_AUTH_ACCOUNT_EXISTS => '第三方账号已存在',
            
            /* SMS */
            self::CODE_SMS_AUTH_FAILED => '验证码不匹对',
            self::CODE_SMS_INVALID => '验证码已失效',
            self::CODE_SMS_SEND_FAILED => '发送失败',
            self::CODE_SMS_TEMPLATE_NOT_FOUND => '找不到对应模板',
        ];
    }
}
