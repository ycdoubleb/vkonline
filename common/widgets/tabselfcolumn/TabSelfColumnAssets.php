<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\widgets\tabselfcolumn;

use yii\web\AssetBundle;

/**
 * Description of TreegridAssets
 *
 * @author Administrator
 */
class TabSelfColumnAssets extends AssetBundle{
    public $sourcePath = '@common/widgets/tabselfcolumn/assets';
    public $css = [
       'css/tabselfcolumn.css',
       'css/kv-bootstrap-notify.css'
    ];
    public $js = [
        'js/TabSelfColumn.js',
        'js/bootstrap-notify.js'
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
