<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\widgets\imgdropdown;

use yii\web\AssetBundle;

/**
 * Description of TreegridAssets
 *
 * @author Administrator
 */
class ImgDropdownAssets extends AssetBundle{
    public $sourcePath = '@common/widgets/imgdropdown/assets';
    public $css = [
       'css/imgdropdown.css',
    ];
    public $js = [
        'js/imgdropdown.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
