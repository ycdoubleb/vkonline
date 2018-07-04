<?php

namespace common\widgets\watermark;

use yii\web\AssetBundle;

class WatermarkAsset extends AssetBundle
{
    public $css = [
        'css/watermark.css',
    ];
    public $js = [
        'js/watermark.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset',
    ];
    
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__;
        parent::init();
    }
}
