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
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{User}{List}',[
    'User' => Yii::t('app', 'User'),
    'List' => Yii::t('app', 'List'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create main">
    <div class="frame">
        <div class="frame-title">
            <i class="icon fa fa-edit"></i>
            <span><?= Yii::t('app', '{Create}{User}',[
                'Create' => Yii::t('app', 'Create'),
                'User' => Yii::t('app', 'User'),
            ]) ?></span>
        </div>
        <?= $this->render('_form', [
            'model' => $model,
            'customer' => $customer,
        ]) ?>
    </div>
</div>

<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>
