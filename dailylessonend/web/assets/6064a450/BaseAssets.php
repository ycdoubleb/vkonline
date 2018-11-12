<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\assets;

use yii\web\AssetBundle;
use const YII_DEBUG;

/**
 * Description of DemandAssets
 *
 * @author Administrator
 */
class BaseAssets extends AssetBundle{
    //put your code here
    public $sourcePath = '@frontend/assets';
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
    public $css = [
        'css/base.css',
    ];
    public $js = [
        
    ];
}
