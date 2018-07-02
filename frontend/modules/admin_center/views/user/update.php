<?php

use common\models\User;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model User */

ModuleAssets::register($this);

$this->title = Yii::t('app', "{Update}{User}：{$model->nickname}", [
    'Update' => Yii::t('app', 'Update'), 'User' => Yii::t('app', 'User'),
]);

?>
<div class="user-update main">
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

<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    
?>
