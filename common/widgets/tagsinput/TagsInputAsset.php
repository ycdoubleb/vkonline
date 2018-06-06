<?php

namespace common\widgets\tagsinput;

use yii\web\AssetBundle;

class TagsInputAsset extends AssetBundle
{
    public $css = [
        'css/amazeui.css',
        'css/amazeui.tagsinput.css',
    ];
    public $js = [
        'js/amazeui.tagsinput.min.js',
        'js/typeahead.min.js',
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
