<?php

use common\models\User;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model User */

$this->title = Yii::t('app', '{Create}{User}',[
    'Create' => Yii::t('app', 'Create'),
    'User' => Yii::t('app', 'User'),
]);

?>
<div class="user-create main">
    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Create}{User}',[
                    'Create' => Yii::t('app', 'Create'),
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
