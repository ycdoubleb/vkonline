<?php

namespace backend\modules\rediscache_admin\assets;

use yii\web\AssetBundle;
use const YII_DEBUG;

/**
 * Main backend application asset bundle.
 */
class RedisCacheAdminAsset extends AssetBundle
{
    public $sourcePath = '@backend/modules/rediscache_admin/assets';
    public $baseUrl = '@backend/modules/rediscache_admin/assets';
    public $css = [
        'css/css.css',
    ];
    public $js = [
        'js/wskeee.stringutils.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
