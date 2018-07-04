<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\utils;

/**
 * Description of StringUtil
 *
 * @author Kiwi°
 */
class StringUtil {
    //put your code here
    /**
     * 补全文件路径
     * @param srting $path  路径
     * @param string|array $withStr 指定的字符，默认['http://', 'https://', '/']
     * @param srting $appendStr  补全的字符，默认‘/’
     * @return srting
     */
    public static function completeFilePath($path, $withStr = '', $appendStr = '/')
    {
        //如果$withStr为空的，默认['http://', 'https://', '/']
        if(empty($withStr)){
            $withStr = ['http://', 'https://', '/'];
        }
        
        //如果$withStr不是数组，默认转为数组
        if(!is_array($withStr)){
            $withStr = [$withStr];
        }
        //如果参数path为空，默认为空字符串
        if($path == null){
            $path = '';
        }
        //判断指定的字符串是否存在，若不存在则补全
        $isAppendStr = false;
        foreach ($withStr as $str) {
            if(stripos($path, "$str") !== 0){
                $isAppendStr = true;
            }else{
                $isAppendStr = false;
                break;
            }
        }
        return $isAppendStr ? $appendStr . $path : $path;
    }
}
