<?php

use common\models\vk\Teacher;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Teacher */


ModuleAssets::register($this);

$this->title = Yii::t('app', '{Create}{Teacher}', [
    'Create' => Yii::t('app', 'Create'), 'Teacher' => Yii::t('app', 'Teacher')
]);

?>
<div class="teacher-create main">
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

