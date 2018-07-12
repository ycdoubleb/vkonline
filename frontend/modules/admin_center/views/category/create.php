<?php

use common\models\vk\Category;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Category */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Create}{Category}',[
    'Create' => Yii::t('app', 'Create'),  'Category' => Yii::t('app', 'Category'),
]);
?>
<div class="category-create main">
    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!-- 表单 -->
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
