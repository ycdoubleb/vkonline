<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\widgets\charts;

use yii\web\AssetBundle;

/**
 * Description of StatisticsAsset
 *
 * @author Administrator
 */
class ChartAsset extends AssetBundle{
    //put your code here
    public $sourcePath = '@common/widgets/charts';
    public $publishOptions = [
        'forceCopy'=>YII_DEBUG
    ];  
    public $css = [
       'css/statistics.css',
    ];
    public $js = [
        'js/echarts.min.js',
        'js/chart.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
}
