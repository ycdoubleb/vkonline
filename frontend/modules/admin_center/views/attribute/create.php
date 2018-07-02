<?php

use common\models\vk\CourseAttribute;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model CourseAttribute */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Add}{Attribute}',[
    'Add' => Yii::t('app', 'Add'), 'Attribute' => Yii::t('app', 'Attribute'),
]);

?>
<div class="course-attribute-create main">
    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!-- 表单 -->
    <?= $this->render('_form', [
        'model' => $model,
        'path' => $path,
    ]) ?>
</div>