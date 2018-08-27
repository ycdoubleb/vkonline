<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\help_center\assets;

use yii\web\AssetBundle;
use const YII_DEBUG;

/**
 * Description of DemandAssets
 *
 * @author Administrator
 */
class HelpCenterAssets extends AssetBundle {

    //put your code here
    public $sourcePath = '@frontend/modules/help_center/assets';
    public $depends = [
        'yii\web\YiiAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
    public $css = [
        'css/layout.css',
        'css/module.css',
        'css/tree_menu.css',
    ];
    public $js = [
        'js/view.js',
        'js/tree_menu.js',
    ];

}
