<?php

use frontend\modules\admin_center\assets\MainAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */


$this->title = Yii::t('app', '{Admin}{Center}',[
    'Admin' => Yii::t('app', 'Admin'),'Center' => Yii::t('app', 'Center'),
]);

echo $this->render('@frontend/modules/build_course/views/layouts/main', ['content' => $content]); 

?>
