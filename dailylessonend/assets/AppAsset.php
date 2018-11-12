<?php

namespace dailylessonend\assets;

use yii\web\AssetBundle;

/**
 * Main dailylessonend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $sourcePath = '@dailylessonend/assets';
    
    public $css = [
        'css/base.css',
        'css/common.css',
    ];
    public $js = [
        //'js/hm.js',     //百度站点统计
        'js/wskeee.stringutils.js',  //渲染
        'js/wskeee.dateUtils.js'  //日期格式
    ];
    public $depends = [
        'rmrevin\yii\fontawesome\AssetBundle',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
