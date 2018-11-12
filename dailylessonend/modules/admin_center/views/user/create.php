<?php

use common\models\User;
use dailylessonend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model User */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Create}{User}',[
    'Create' => Yii::t('app', 'Create'), 'User' => Yii::t('app', 'User'),
]);

?>
<div class="user-create main">
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