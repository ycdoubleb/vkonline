<?php

use common\models\User;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model User */

$this->title = Yii::t('app', '{Update}{User}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'User' => Yii::t('app', 'User'),
    'nameAttribute' => $model->id,
]);

?>
<div class="user-update main">
    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Update}{User}',[
                    'Update' => Yii::t('app', 'Update'),
                    'User' => Yii::t('app', 'User'),
                ]) ?></span>
            </div>
            <div class="content-content">
                <?= $this->render('_form', [
                    'model' => $model,
                    'customer' => $customer,
                ]) ?>
            </div>
        </div>
    </div>
</div>

<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>
