<?php

use common\models\User;
use dailylessonend\modules\user\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model User */

ModuleAssets::register($this);

?>

<div class="user-update main">
    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Edit}{Basic}{Info}', [
                    'Edit' => Yii::t('app', 'Edit'), 'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info')
                ]) ?></span>
            </div>
            <div class="content-content">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>
