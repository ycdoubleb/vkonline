<?php

namespace common\widgets\tagsinput;

use yii\web\AssetBundle;

class TagsInputAsset extends AssetBundle
{
    public $css = [
        'css/bootstrap/bootstrap-tagsinput.css',
        'css/bootstrap/app.css',
    ];
    public $js = [
        'js/bootstrap/bootstrap-tagsinput.js',
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
