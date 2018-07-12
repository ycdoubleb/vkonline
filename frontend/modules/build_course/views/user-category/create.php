<?php

use common\models\vk\UserCategory;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model UserCategory */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{My}{Video} / {Create}{Catalog}',[
    'My' => Yii::t('app', 'My'),  'Video' => Yii::t('app', 'Video'),
    'Create' => Yii::t('app', 'Create'),  'Catalog' => Yii::t('app', 'Catalog'),
]);
?>
<div class="user-category-create main">

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
