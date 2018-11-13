<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace dailylessonend\assets;

use yii\web\AssetBundle;
use const YII_DEBUG;

/**
 * Description of TimerButtonAssets
 *
 * @author Administrator
 */
class ClipboardAssets extends AssetBundle{
    //put your code here
    public $sourcePath = '@dailylessonend/assets';
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
    public $css = [
        
    ];
    public $js = [
        'js/clipboard.min.js',
    ];
}
