<?php

namespace frontend\modules\cm_my_material;

/**
 * cm_my_material module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'frontend\modules\cm_my_material\controllers';
    
    public $layout = '@frontend/views/layouts/cm_main';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
