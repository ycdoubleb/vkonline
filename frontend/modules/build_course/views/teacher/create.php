<?php

use common\models\vk\Teacher;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Teacher */


ModuleAssets::register($this);

?>
<div class="teacher-create main">
    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{Create}{Teacher}', [
                'Create' => Yii::t('app', 'Create'), 'Teacher' => Yii::t('app', 'Teacher')
            ]) ?>
        </span>
    </div>
    <!--表单-->
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

