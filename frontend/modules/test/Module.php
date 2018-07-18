<?php

namespace frontend\modules\test;

/**
 * test module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'frontend\modules\test\controllers';

    public $layout = "@frontend/views/layouts/main_no_nav";
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
