<?php

use common\models\vk\Teacher;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Teacher */


ModuleAssets::register($this);

?>
<div class="teacher-update main">

    <div class="crumbs">
        <i class="fa fa-pencil"></i>
        <span><?= Yii::t('app', '{Update}{Teacher}', [
            'Update' => Yii::t('app', 'Update'), 'Teacher' => Yii::t('app', 'Teacher')
        ]) ?></span>
    </div>
    
    <?= $this->render('_form', [
        'model' => $model,
        'allTags' => $allTags,
        'tagsSelected' => $tagsSelected,
    ]) ?>

</div>

