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
class MainAssets extends AssetBundle {
    public $sourcePath = '@frontend/modules/res_service/assets';
    public $css = [
       'css/main.css'
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
