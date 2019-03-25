<?php

namespace frontend\modules\cm_material_library;

/**
 * cm_material_library module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'frontend\modules\cm_material_library\controllers';
    
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
