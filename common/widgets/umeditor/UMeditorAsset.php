<?php

namespace common\widgets\umeditor;

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
class UMeditorAsset extends AssetBundle 
{
    //public $basePath = '@webroot/assets';
    //public $baseUrl = '@web/assets';
    public $sourcePath = '@common/widgets/umeditor';
    public $publishOptions = [
        'forceCopy'=>YII_DEBUG
    ];
    public $css = [
        'themes/default/css/umeditor.css',
    ];
    public $js = [
        'umeditor.config.js',
        'umeditor.min.js',
        'lang/zh-cn/zh-cn.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
   
}
