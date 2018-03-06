<?php

namespace common\widgets\ueditor;

use yii\web\AssetBundle;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UeditorAsset
 *
 * @author KiwiUser
 */
class UeditorAsset extends AssetBundle 
{
    //public $basePath = '@webroot/assets';
    //public $baseUrl = '@web/assets';
    public $sourcePath = '@common/widgets/ueditor';
    public $publishOptions = [
        'forceCopy'=>YII_DEBUG
    ];
    public $css = [
       // 'themes/default/css/umeditor.css',
    ];
    public $js = [
        'ueditor.config.js',
        'ueditor.all.min.js',
        'lang/zh-cn/zh-cn.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
   
}
