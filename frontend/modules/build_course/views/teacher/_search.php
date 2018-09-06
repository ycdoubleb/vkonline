<?php

use common\models\vk\UserCategory;
use common\utils\StringUtil;
use common\widgets\depdropdown\DepDropdown;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

?>

<div class="teacher-form vk-form"> 
        
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options'=>[
            'id' => 'build-course-form',
            'class'=>'form-horizontal',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    
    <div class="col-lg-12 col-md-12">
        
        <!--老师名称-->
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true,
            'onchange' => 'submitForm();',
        ])->label(Yii::t('app', '{Teacher}{Name}：', [
            'Teacher' => Yii::t('app', 'Teacher'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
        
        <!--认证状态-->
        <?= $form->field($searchModel, 'is_certificate')->radioList(['' => '全部', 1 => '已认证', 0 => '未认证'], [
            'value' => ArrayHelper::getValue($filters, 'TeacherSearch.is_certificate', ''),
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
        ])->label(Yii::t('app', '{Authentication}{Status}：', [
            'Authentication' => Yii::t('app', 'Authentication'), 'Status' => Yii::t('app', 'Status')
        ])) ?>
        
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
    //提交表单 
    window.submitForm = function(){
        $('#build-course-form').submit();
    }  
JS;
    $this->registerJs($js,  View::POS_READY);
?>