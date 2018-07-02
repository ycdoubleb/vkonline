<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\webuploader\models;

use common\models\api\ApiResponse;

/**
 * Description of UploadResponse
 *
 * @author Administrator
 */
class UploadResponse extends ApiResponse {

    //--------------------------------------------------------------------------------------------------------------
    //
    // 上传组件
    // 
    //--------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------
    // 文件
    //--------------------------------------------------------------------------
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
    const CODE_FILE_CHUNKS_NOT_FOUND = '20003';

    /**
     * 文件保存失败
     */
    const CODE_FILE_SAVE_FAIL = '20004';

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
    //--------------------------------------------------------------------------
    // 分片
    //--------------------------------------------------------------------------
    /**
     * 文件分片已存在
     */
    const CODE_CHUNK_EXIT = '20101';

    /**
     * 合并文件时，查找分片不存在
     */
    const CODE_CHUNK_NOT_FOUND = '20102';
    //--------------------------------------------------------------------------
    // 目录
    //--------------------------------------------------------------------------
    /**
     * 目录不存在
     */
    const CODE_DIR_NOT_FOUND = '20201';
    //--------------------------------------------------------------------------
    // 上传外部联接
    //--------------------------------------------------------------------------
    /**
     * 获取数据失败
     */
    const CODE_LINK_GET_DATA_FAIL = '20301';
    
    //--------------------------------------------------------------------------
    // OSS
    //--------------------------------------------------------------------------
    /**
     * 上传OSS错误
     */
    const CODE_UPLOAD_OSS_FAIL = '20401';

    /**
     * 返回 code 与 反馈修改的对应关系
     * 
     */
    public function getCodeMap() {
        return parent::getCodeMap() + [
            //文件
            self::CODE_FILE_EXIT => '文件已存在！',
            self::CODE_FILE_INTERRUPT => '文件上传未完成，请继续上传其它分片！',
            self::CODE_FILE_CHUNKS_NOT_FOUND => '合并文件失败，找不到对应分片数据！',
            self::CODE_FILE_SAVE_FAIL => '合并文件失败!',
            self::CODE_READ_INPUT_FILE_FAIL => '无法读取文件：{name}',
            self::CODE_READ_INPUT_STREAM_FAIL => '无法读取输入流：{name}',
            self::CODE_OPEN_OUPUT_STEAM_FAIL => '系统错误，无法建立临时输出流：{name}',
            self::CODE_MOVE_INPUT_FILE_FAIL => '系统错误，无法移动上传文件：{name}',
            //分片
            self::CODE_CHUNK_EXIT => '分片已存在！',
            self::CODE_CHUNK_NOT_FOUND => '分片不存在：{chunkPath}',
            //目录
            self::CODE_DIR_NOT_FOUND => '目录不存在：{path}',
            //上传路径
            self::CODE_LINK_GET_DATA_FAIL => '获取远程数据失败！',
            //OSS
            self::CODE_UPLOAD_OSS_FAIL => '上传OSS错误',
        ];
    }

}
