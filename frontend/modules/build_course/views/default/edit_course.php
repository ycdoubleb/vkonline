<?php

use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

?>
<div class="course-update main">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
