<?php

use frontend\modules\admin_center\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

ModuleAssets::register($this);

$this->title = Yii::t('app', 'Task');

?>

<div class="default-index main">
    <!-- 页面标题 -->
    <div class="vk-title">
        <span><?= $this->title ?></span>
    </div>
    <div class="vk-panel">
        <?= Html::img('/imgs/admin_center/images/404.jpg', ['width' => '100%', 'height' => '100%']) ?>
    </div>
</div>