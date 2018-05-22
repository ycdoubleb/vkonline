<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\study_center\assets;

use yii\web\AssetBundle;
use const YII_DEBUG;

/**
 * Description of HDatepickerAssets
 *
 * @author Administrator
 */
class PalyAssets extends AssetBundle {
    public $sourcePath = '@frontend/modules/study_center/assets';
    public $css = [
       'css/palypage.css'
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
