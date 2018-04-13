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

    <div class="crumbs">
        <i class="fa fa-pencil"></i>
        <span><?= Yii::t('app', '{Update}{Course}', [
            'Update' => Yii::t('app', 'Update'), 'Course' => Yii::t('app', 'Course')
        ]) ?></span>
    </div>
    
    <?= $this->render('_form', [
        'model' => $model,
        'allCategory' => $allCategory,
        'allTeacher' => $allTeacher,
        'allTags' => $allTags,
        'tagsSelected' => $tagsSelected,
    ]) ?>

</div>
