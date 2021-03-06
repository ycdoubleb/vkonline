<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\res_service\assets;

use yii\web\AssetBundle;
use const YII_DEBUG;

/**
 * Description of HDatepickerAssets
 *
 * @author Administrator
 */
class ModuleAssets extends AssetBundle {
    public $sourcePath = '@frontend/modules/res_service/assets';
    public $css = [
       'css/module.css',
       'css/fileInput.css',
    ];
    public $js = [
        'js/clipboard.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
