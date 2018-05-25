<?php

namespace common\widgets\share;

use yii\web\AssetBundle;

class ShareAsset extends AssetBundle
{
    public $css = [
        'css/share.css'
    ];
    public $js = [
        'js/jquery-qrcode-0.14.0.min.js',
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
