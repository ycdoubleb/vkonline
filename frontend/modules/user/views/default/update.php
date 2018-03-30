<?php

use common\models\User;
use frontend\modules\user\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model User */


ModuleAssets::register($this);

//$this->title = Yii::t('app', '{Update}{User}: {nameAttribute}', [
//    'Update' => Yii::t('app', 'Update'),
//    'User' => Yii::t('app', 'User'),
//    'nameAttribute' => $model->id,
//]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{User}{List}',[
//    'User' => Yii::t('app', 'User'),
//    'List' => Yii::t('app', 'List'),
//]), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="user-default-update main">

    <div class="crumbs">
        <i class="fa fa-pencil"></i>
        <span><?= Yii::t('app', '{Edit}{Basic}{Info}', [
            'Edit' => Yii::t('app', 'Edit'), 'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info')
        ]) ?></span>
    </div>
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
