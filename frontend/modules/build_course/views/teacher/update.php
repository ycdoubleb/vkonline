<?php

use common\models\vk\Teacher;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Teacher */


ModuleAssets::register($this);

$this->title = Yii::t('app', "{Update}{Teacher}：{$model->name}", [
    'Update' => Yii::t('app', 'Update'), 'Teacher' => Yii::t('app', 'Teacher')
]);

?>
<div class="teacher-update main">

    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!--表单-->
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
   
</div>

