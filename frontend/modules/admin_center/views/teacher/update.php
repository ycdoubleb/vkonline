<?php

use common\models\vk\Teacher;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Teacher */


ModuleAssets::register($this);

?>
<div class="teacher-update main">

    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{Update}{Teacher}', [
                'Update' => Yii::t('app', 'Update'), 'Teacher' => Yii::t('app', 'Teacher')
            ]) ?>
        </span>
    </div>
    <!--表单-->
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
   
</div>
