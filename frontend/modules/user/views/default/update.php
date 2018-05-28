<?php

use common\models\User;
use frontend\modules\user\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model User */

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

<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>