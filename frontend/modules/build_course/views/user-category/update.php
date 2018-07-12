<?php

use common\models\vk\UserCategory;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model UserCategory */

ModuleAssets::register($this);

$this->title = Yii::t('app', "{My}{Video} / {Update}{Catalog}：{$model->name}",[
    'My' => Yii::t('app', 'My'),  'Video' => Yii::t('app', 'Video'),
    'Update' => Yii::t('app', 'Update'),  'Catalog' => Yii::t('app', 'Catalog'),
]);
?>
<div class="user-category-update main">

    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
