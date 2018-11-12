<?php

use common\models\vk\CustomerWatermark;
use common\models\vk\searchs\CustomerWatermarkSearch;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CustomerWatermarkSearch */
/* @var $form ActiveForm */
?>

<div class="customer-watermark-search">

    <div class="course-form vk-form set-spacing"> 
        
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options'=>[
                'id' => 'admin-center-form',
                'class'=>'form-horizontal',
            ],
            'fieldConfig' => [  
                'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n",  
                'labelOptions' => [
                    'class' => 'col-lg-1 col-md-1 control-label form-label',
                ],  
            ], 
        ]); ?>
        
        <div class="col-log-12 col-md-12">
            
            <!--课程名称-->
            <?= $form->field($searchModel, 'name')->textInput([
                'placeholder' => '请输入...', 'maxlength' => true, 
                'onchange' => 'submitForm();',
            ])->label(Yii::t('app', '{Watermark}{Name}：', [
                'Watermark' => Yii::t('app', 'Watermark'), 'Name' => Yii::t('app', 'Name')
            ])) ?>
            
            <!--水印状态-->
            <?= $form->field($searchModel, 'is_del')->radioList(CustomerWatermark::$statusMap, [
                'value' => ArrayHelper::getValue($filters, 'CustomerWatermarkSearch.is_del', ''),
                'itemOptions'=>[
                    'onclick' => 'submitForm();',
                    'labelOptions'=>[
                        'style'=>[
                            'margin'=>'5px 29px 10px 0px',
                            'color' => '#666666',
                            'font-weight' => 'normal',
                        ]
                    ]
                ],
            ])->label(Yii::t('app', '{Watermark}{Status}：', [
                'Watermark' => Yii::t('app', 'Watermark'), 'Status' => Yii::t('app', 'Status')
            ])) ?>
            
            
        </div>
        
        <?php ActiveForm::end(); ?>
        
    </div>

</div>
