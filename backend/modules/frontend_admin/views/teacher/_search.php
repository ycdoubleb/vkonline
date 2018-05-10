<?php

use common\models\vk\searchs\TeacherSearch;
use common\models\vk\Teacher;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model TeacherSearch */
/* @var $form ActiveForm */
?>

<div class="teacher-search">
    
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label', 'style' => ['padding-left' => '0']],
        ],
    ]); ?>
    
    <div class="search col-lg-12 col-md-12">
        
        <?= $form->field($model, 'name')
            ->label(Yii::t('app', '{Teacher}{Name}：', ['Teacher' => Yii::t('app', 'Teacher'), 'Name' => Yii::t('app', 'Name')])) ?>

        <?= $form->field($model, 'is_certificate')->radioList(Teacher::$certificateStatus, ['class' => 'label-name'])
            ->label(Yii::t('app', '{Authentication}{Status}：', ['Authentication' => Yii::t('app', 'Authentication'),
                'Status' => Yii::t('app', 'Status')]))?>
        
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
        </div>
        
    </div>

    <?php ActiveForm::end(); ?>
    
</div>
