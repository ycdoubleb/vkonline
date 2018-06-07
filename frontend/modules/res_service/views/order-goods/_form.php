<?php

use common\models\vk\Course;
use kartik\datecontrol\DateControl;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Course */
/* @var $form ActiveForm */

?>

<div class="order-goods form set-margin set-bottom">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'res_service-form',
            'class'=>'form-horizontal',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
   
    <!--订单名称-->
    <?= $form->field($model, 'name')->textInput([
        'placeholder' => '请输入...', 'maxlength' => true
    ])->label(Yii::t('app', '{orderGoods}{Name}', [
        'orderGoods' => Yii::t('app', 'Order Goods'), 'Name' => Yii::t('app', 'Name')
    ])) ?>
    <!--开始时间-->
    <?= $form->field($model, 'created_at', [
        'template' => "{label}\n<div class=\"col-lg-5 col-md-5\">{input}</div>\n<div class=\"col-lg-5 col-md-5\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label form-label'],
    ])->widget(DateControl::class,[
        'type'=> DateControl::FORMAT_DATETIME,
        'displayFormat' => 'yyyy-MM-dd H:i',
        'saveFormat' => 'yyyy-MM-dd H:i',
        'ajaxConversion'=> true,
        'autoWidget' => true,
        'readonly' => false,
        'widgetOptions' => [
            'pluginOptions' => ['autoclose' => true,],
        ],
    ])->label(Yii::t('app', 'Start Time')) ?>
    <!--结束时间-->
    <?= $form->field($model, 'updated_at', [
        'template' => "{label}\n<div class=\"col-lg-5 col-md-5\">{input}</div>\n<div class=\"col-lg-5 col-md-5\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label form-label'],
    ])->widget(DateControl::class,[
        'type'=> DateControl::FORMAT_DATETIME,
        'displayFormat' => 'yyyy-MM-dd H:i',
        'saveFormat' => 'yyyy-MM-dd H:i',
        'ajaxConversion'=> true,
        'autoWidget' => true,
        'readonly' => false,
        'widgetOptions' => [
            'pluginOptions' => ['autoclose' => true,],
        ],
    ])->label(Yii::t('app', 'End Time')) ?>
    <!--描述-->
    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea(['value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 6, 'placeholder' => '请输入...']) ?>
    
    <div class="form-group">
        <?= Html::label(null, null, ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?= Html::button(Yii::t('app', 'Submit'), ['id' => 'submitsave', 'class' => 'btn btn-success btn-flat']) ?>
        </div> 
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = 
<<<JS
    
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>