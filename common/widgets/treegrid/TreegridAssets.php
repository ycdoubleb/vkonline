<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\widgets\treegrid;

use yii\web\AssetBundle;

/**
 * Description of TreegridAssets
 *
 * @author Administrator
 */
class TreegridAssets extends AssetBundle{
    public $sourcePath = '@common/widgets/treegrid';
    public $css = [
       'css/jquery.treegrid.css'
    ];
    public $js = [
        'js/jquery.treegrid.min.js',
        'js/jquery.treegrid.bootstrap3.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
