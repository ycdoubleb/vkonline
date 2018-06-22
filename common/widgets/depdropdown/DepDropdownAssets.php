<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\widgets\depdropdown;

use yii\web\AssetBundle;

/**
 * Description of TreegridAssets
 *
 * @author Administrator
 */
class DepDropdownAssets extends AssetBundle{
    public $sourcePath = '@common/widgets/depdropdown/assets';
    public $css = [
       'css/depdropdown.css',
    ];
    public $js = [
        'js/DepDropdown.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
