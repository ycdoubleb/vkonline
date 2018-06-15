<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\webuploader\models;

/**
 * Description of UploadResponse
 *
 * @author Administrator
 */
class UploadResponse {
    /****************************
     * 公共
     ****************************/
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
     * 未知错误
     */
    const CODE_COMMON_UNKNOWN = '10099';
    
    
    /****************************
     * 文件
     ****************************/
    /**
     * 文件已存在
     */
    const CODE_FILE_EXIT = '20001';
    /**
     * 文件上传过程中因不明原因中断上传
     */
    const CODE_FILE_INTERRUPT = '20002';
    /**
     * 合并文件失败，找不到对应分片数据
     */
    const CODE_FILE_CHUNKS_NOT_FOUND= '20003';
    /**
     * 文件保存失败
     */
    const CODE_FILE_SAVE_FAIL= '20004';
    
    /**
     * 读取上传文件失败
     */
    const CODE_READ_INPUT_FILE_FAIL = '20010';
    /**
     * 读取上传文件流失败
     */
    const CODE_READ_INPUT_STREAM_FAIL = '20011';
    /**
     * 打开输出流失败
     */
    const CODE_OPEN_OUPUT_STEAM_FAIL = '20012';
    /**
     * 系统错误，无法移动上传文件
     */
    const CODE_MOVE_INPUT_FILE_FAIL = '20013';
    
    
    
    /****************************
     * 分片
     ****************************/
    /**
     * 文件分片已存在
     */
    const CODE_CHUNK_EXIT = '20101';
    /**
     * 合并文件时，查找分片不存在
     */
    const CODE_CHUNK_NOT_FOUND = '20102';
    
    /****************************
     * 目录
     ****************************/
    /**
     * 目录不存在
     */
    const CODE_DIR_NOT_FOUND = '20201';
    
    
    static $codeMap = [
        //公共
        self::CODE_COMMON_OK => 'OK',
        self::CODE_COMMON_MISS_PARAM => '缺少参数：{param}',
        self::CODE_COMMON_UNKNOWN => '未知错误！',
        //文件
        self::CODE_FILE_EXIT => '文件已存在！',
        self::CODE_FILE_INTERRUPT => '文件上传未完成，请继续上传其它分片！',
        self::CODE_FILE_CHUNKS_NOT_FOUND => '合并文件失败，找不到对应分片数据！',
        self::CODE_FILE_SAVE_FAIL => '合并文件失败：！',
        self::CODE_READ_INPUT_FILE_FAIL => '无法读取文件：{name}',
        self::CODE_READ_INPUT_STREAM_FAIL => '无法读取输入流：{name}',
        self::CODE_OPEN_OUPUT_STEAM_FAIL => '系统错误，无法建立临时输出流：{name}',
        self::CODE_MOVE_INPUT_FILE_FAIL => '系统错误，无法移动上传文件：{name}',
        //分片
        self::CODE_CHUNK_EXIT => '分片已存在！',
        self::CODE_CHUNK_NOT_FOUND => '分片不存在：{chunkPath}',
        //目录
        self::CODE_DIR_NOT_FOUND => '目录不存在：{path}',
    ];
    
    /**
     * 创建一个API响应
     * @param string $code      操作代码，0成功，其它失败 eg: UploadResponse::CODE_OK
     * @param string $msg       自定义信息
     * @param string|array|object $data     返回主体数据
     * @param array $params     自定义信息中动态传参
     * @return array [code,msg,data]
     */
    static public function create($code = self::CODE_OK, $msg = null, $data = null , $params = null){
        //不自定将使用预定义格式 
        $msg = $msg == null ? self::$codeMap[$code] : $msg;
        //如果params不为空，将替换消息里的动态参数
        if($params != null){
            foreach($params as $key => $value){
                $msg = preg_replace("/\{$key\}/",$value,$msg);
            }
        }
        //生成返回格式
        $returnData = [
            'code'=> $code,  
            'msg'=> $msg,  
        ];
        //data不为空时加入格式
        if($data != null){
            $returnData['data'] = $data;
        }
        return $returnData;
    }
}
