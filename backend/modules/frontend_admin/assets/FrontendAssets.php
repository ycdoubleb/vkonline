<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\modules\frontend_admin\assets;

use yii\web\AssetBundle;

/**
 * Description of HDatepickerAssets
 *
 * @author Administrator
 */
class FrontendAssets extends AssetBundle {
    public $sourcePath = '@backend/modules/frontend_admin/assets';
    public $css = [
       'css/module.css',
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
