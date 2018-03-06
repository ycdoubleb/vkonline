<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\rbac\assets;

use yii\web\AssetBundle;

/**
 * Description of FrameworkImportAsset
 *
 * @author Administrator
 */
class RouteAsset extends AssetBundle{
    public $sourcePath = '@common/modules/rbac/assets';
    public $css = [
       'css/route.css'
    ];
    public $js = [
        'js/route.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
