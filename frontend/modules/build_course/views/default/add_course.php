<?php

use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;


/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

?>
<div class="course-create main">

    <?= $this->render('_form_course', [
        'model' => $model,
        'allCategory' => $allCategory,
        'allTeacher' => $allTeacher,
    ]) ?>

</div>

