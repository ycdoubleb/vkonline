<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\build_course\assets;

use yii\web\AssetBundle;
use const YII_DEBUG;

/**
 * Description of HDatepickerAssets
 *
 * @author Administrator
 */
class VideoImportAssets extends AssetBundle {
    public $sourcePath = '@frontend/modules/build_course/assets';
    public $css = [
       'css/video_import.css',
       'css/select2.min.css'
    ];
    public $js = [
        'js/video-batch-upload.js',
        'js/select2.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
