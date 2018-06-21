<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\api;

/**
 * Description of ApiResponse
 * @property string $code                   响应代码                        
 * @property msg    $msg                    返馈信息
 * @property array|string|object $data      响应数据
 * @author Administrator
 */
class ApiResponse {
    
    //--------------------------------------------------------------------------------------------------------------
    //
    // 公共 CODE
    // 
    //--------------------------------------------------------------------------------------------------------------
    /* 操作成功 
     * 大于0为操作失败，可能为权限不够，也可能查找资源不存在等等
     */
    const CODE_COMMON_OK = "0";
    /**
     * 缺少参数
     * eg：缺少 fileMD5或者chunkMD5
     */
    const CODE_COMMON_MISS_PARAM = '10001';
    /**
     * 保存DB出错
     */
    const CODE_COMMON_SAVE_DB_FAIL = '10002';
    
    /**
     * 未知错误
     */
    const CODE_COMMON_UNKNOWN = '10099';
    
    
    
    //--------------------------------------------------------------------------------------------------------------
    //
    // 变量
    // 
    //--------------------------------------------------------------------------------------------------------------
    public $code = 0;
    public $msg = '';
    public $data = null;
    
    /**
     * 创建一个API响应
     * @param string $code      操作代码，0成功，其它失败 eg: UploadResponse::CODE_OK
     * @param string $msg       自定义信息
     * @param string|array|object $data     返回主体数据
     * @param array $params     自定义信息中动态传参
     * @return array [code,msg,data]
     */
    public function __construct($code = self::CODE_OK, $msg = null, $data = null , $params = null) {
        $codeMap = $this->getCodeMap();
        //不自定将使用预定义格式 
        $msg = $msg == null ? (isset($codeMap[$code]) ? $codeMap[$code] : '') : $msg;
        //如果params不为空，将替换消息里的动态参数
        if($params != null){
            foreach($params as $key => $value){
                $msg = preg_replace("/\{$key\}/",$value,$msg);
            }
        }
        $this->code = $code;
        $this->msg = $msg;
        $this->data = $data;
    }
    
    /**
     * 返回 code 与 反馈修改的对应关系
     * 使用时由子类合并使用，注意：请使用  + 号合并数组，保留原来键值
     */
    public function getCodeMap(){
        return [
            //公共
            self::CODE_COMMON_OK => 'OK',
            self::CODE_COMMON_MISS_PARAM => '缺少参数：{param}',
            self::CODE_COMMON_SAVE_DB_FAIL => '保存DB出错！',
            self::CODE_COMMON_UNKNOWN => '未知错误！',
        ];
    }
}
