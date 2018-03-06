<?php

use backend\modules\system_admin\assets\HolidayAssets;
use common\models\Holiday;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Holiday */
/* @var $form ActiveForm */
?>

<div class="holiday-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList(Holiday::TYPE_MAP)?>

    <?= $form->field($model, 'year')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Holiday Year Placeholder')]) ?>
    
    <div class="form-group field-holiday-date">
        <label class="control-label" for="holiday-date"><?= Yii::t('app', 'Holiday Date') ?></label>
        <br/>
        <?= Html::activeInput('text', $model, 'date', ['class' => 'form-control' , 'style' => 'max-width:200px;display:inline-block;']) ?>
        <div id="lunar-container" style="display: inline-block">
            <?= Html::activeCheckbox($model, 'is_lunar') ?>
            <label id="date-help-block" style="color:#ccc;font-style:normal;"></label>
        </div>
        
    </div>

    <?= $form->field($model, 'des')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'is_publish')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ]
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php 
    $js = <<<JS
            //验证时间段正则
            var dateRangReg = /^\d{8}\s\-\s\d{8}$/;
            //验证简单日期正则
            var dateReg = /^\d{4}$/;
            
            $("#holiday-date").on("blur",function(){
                var value = $(this).val();
                if($("#holiday-type").val() == 2){
                    if(value.match(dateReg) == null){
                        $("#date-help-block").html("( 格式有误！请输入以下格式：0405 )");
                    }else{
                        var month = Number(value.substr(0,2));
                        var day = Number(value.substr(2,2));
                        try{
                            $("#date-help-block").html($("#holiday-is_lunar").prop("checked") ? "("+Lunar.chineseMonth(month)+Lunar.chineseNumber(day)+")" : "");
                        }catch(e){
                            $("#date-help-block").html("( 格式有误！请输入以下格式：0405 )");
                        }
                        
                    }
                }else{
                    if(value.match(dateRangReg) == null){
                        $("#date-help-block").html("( 格式有误！请输入以下格式：20180112 - 20180115 )");
                    }else{
                        $("#date-help-block").html("");
                    }
                }
            });
            
            $("#holiday-is_lunar").on("change",function(){
                $("#holiday-date").trigger("blur");
            });
            $("#holiday-type").on("change",function(){
                if($("#holiday-date").val() !="")
                    $("#holiday-date").trigger("blur");
            });
            $("#holiday-date").trigger("blur");
JS;
    $this->registerJs($js, View::POS_READY);
    HolidayAssets::register($this);
?>
