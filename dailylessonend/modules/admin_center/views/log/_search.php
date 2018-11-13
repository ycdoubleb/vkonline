<?php

use common\models\vk\Log;
use common\models\vk\searchs\LogSearch;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\widgets\ActiveForm;



/* @var $this View */
/* @var $searchModel LogSearch */
/* @var $form ActiveForm */

?>

<div class="log-search vk-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options'=>[
            'id' => 'admin-center-form',
            'class'=>'form-horizontal',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
            'labelOptions' => [
                'class' => 'col-lg-2 col-md-2 control-label form-label',
            ],  
        ], 
    ]); ?>
    
    <div class="col-log-6 col-md-6">
        
        <!--级别-->
        <?= $form->field($searchModel, 'level')->radioList(ArrayHelper::merge(['' => '全部'], Log::$levelMap), [
            'value' => ArrayHelper::getValue(Yii::$app->request->queryParams, 'LogSearch.level', ''),
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
        ])->label(Yii::t('app', '{Grade}：', ['Grade' => Yii::t('app', 'Grade')])) ?>
        
        <!--分类-->
        <?= $form->field($searchModel, 'category')->widget(Select2::class, [
            'data' => ArrayHelper::map($dataLogs, 'category', 'category'),
            'options' => ['placeholder' => '请选择...',],
            'pluginOptions' => [
                'allowClear' => true
            ],
            'pluginEvents' => [
                'change' => 'function(){ submitForm(); }'
            ]
        ])->label(Yii::t('app', '{Category}：', ['Category' => Yii::t('app', 'Category')])) ?>
        
        <!--标题-->
        <?= $form->field($searchModel, 'title')->widget(Select2::class, [
            'data' => ArrayHelper::map($dataLogs, 'title', 'title'),
            'options' => ['placeholder' => '请选择...',],
            'pluginOptions' => [
                'allowClear' => true
            ],
            'pluginEvents' => [
                'change' => 'function(){ submitForm(); }'
            ]
        ])->label(Yii::t('app', '{Title}：', ['Title' => Yii::t('app', 'Title')])) ?>
        
        <!--操作人-->
        <?= $form->field($searchModel, 'created_by')->widget(Select2::class, [
            'data' => ArrayHelper::map($dataLogs, 'created_by', 'createdBy.nickname'),
            'options' => ['placeholder' => '请选择...',],
            'pluginOptions' => [
                'allowClear' => true
            ],
            'pluginEvents' => [
                'change' => 'function(){ submitForm(); }'
            ]
        ])->label(Yii::t('app', '{Operation}{People}：', ['Operation' => Yii::t('app', 'Operation'), 'People' => Yii::t('app', 'People')])) ?>
        
    </div>
    
    <div class="col-log-6 col-md-6">
        
        <!--操作时间-->
        <?= $form->field($searchModel, 'created_at')->widget(DateRangePicker::class, [
            'hideInput' => true,
            'convertFormat'=>true,
            'pluginOptions'=>[
                'locale'=>['format' => 'Y-m-d'],
                'allowClear' => true,
                'opens'=>'right',
                'ranges' => [
                    Yii::t('app', "Last Week") => ["moment().startOf('week').subtract(1,'week')", "moment().endOf('week').subtract(1,'week')"],
                    Yii::t('app', "This Week") => ["moment().startOf('week')", "moment().endOf('week')"],
                    Yii::t('app', "Last Month") => ["moment().startOf('month').subtract(1,'month')", "moment().endOf('month').subtract(1,'month')"],
                    Yii::t('app', "This Month") => ["moment().startOf('month')", "moment().endOf('month')"],
                    Yii::t('app', "First Half Year") => ["moment().startOf('year')", "moment().startOf('year').add(5,'month').endOf('month')"],
                    Yii::t('app', "Next Half Year") => ["moment().startOf('year').add(6,'month')", "moment().endOf('year')"],
                    Yii::t('app', "Full Year") => ["moment().startOf('year')", "moment().endOf('year')"],
                ],
            ],
            'pluginEvents' => [
                'change' => 'function(){ submitForm(); }'
            ]
        ])->label(Yii::t('app', '{Operation}{Time}：', ['Operation' => Yii::t('app', 'Operation'), 'Time' => Yii::t('app', 'Time')])) ?>
        
        <!--来源-->
        <?= $form->field($searchModel, 'from')->widget(Select2::class, [
            'data' => ArrayHelper::map($dataLogs, 'from', 'from'),
            'options' => ['placeholder' => '请选择...',],
            'pluginOptions' => [
                'allowClear' => true
            ],
            'pluginEvents' => [
                'change' => 'function(){ submitForm(); }'
            ]
        ])->label(Yii::t('app', '{From}：', ['From' => Yii::t('app', 'From')])) ?>
        
        <!--内容-->
        <?= $form->field($searchModel, 'content')->textInput([
            'onchange' => 'submitForm(); '
        ])->label(Yii::t('app', '{Content}：', ['Content' => Yii::t('app', 'Content')])) ?>
        
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
    /**
     * 提交表单
     */
    window.submitForm = function(){
        $('#admin-center-form').submit();
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>