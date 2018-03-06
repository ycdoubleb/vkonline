<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\modules\system_admin\assets;

use yii\web\AssetBundle;

/**
 * Description of HDatepickerAssets
 *
 * @author Administrator
 */
class HolidayAssets extends AssetBundle {
    public $sourcePath = '@backend/modules/system_admin/assets';
    public $css = [
       
    ];
    public $js = [
        'js/Lunar.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
