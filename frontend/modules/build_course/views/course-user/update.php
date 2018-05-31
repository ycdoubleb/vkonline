<?php

use common\models\vk\CourseUser;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CourseUser */

ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t(null, "{Edit}{HelpMan}", [
    'Edit' => Yii::t('app', 'Edit'), 'HelpMan' => Yii::t('app', 'Help Man')
]);

?>
<div class="course-user-update main modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body form clear">
                
                <?php $form = ActiveForm::begin([
                    'options'=>['id' => 'build-course-form','class'=>'form-horizontal',],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-4 col-md-4\">{input}</div>\n<div class=\"col-lg-4 col-md-4\">{error}</div>",  
                        'labelOptions' => [
                            'class' => 'col-lg-12 col-md-12',
                        ],  
                    ], 
                ]); ?>
                
                <div class="form-group field-user-nickname">
                    <?= Html::label(Yii::t('app', 'Fullname'), 'user-nickname', ['class' => 'col-lg-12 col-md-12']) ?>
                    <div class="col-lg-7 col-md-7">
                        <?= Html::textInput('User[nickname]', $model->user->nickname, [
                            'id' => 'user-nickname', 'class' => 'form-control',
                            'disabled' => true,
                        ]) ?>
                    </div>
                    <div class="col-lg-7 col-md-7"><div class="help-block"></div></div>
                </div>

                <?= $form->field($model, 'privilege')->widget(Select2::class, [
                    'data' => CourseUser::$privilegeMap,
                    'hideSearch' => true,
                    'options' => [
                        'placeholder' => '请选择...'
                    ]
                ])->label(Yii::t(null, '{set}{privilege}',[
                    'set'=> Yii::t('app', 'Set'),'privilege'=> Yii::t('app', 'Privilege')
                ])) ?>

                <?php ActiveForm::end(); ?>
                
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id' => 'submitsave', 'class' => 'btn btn-primary btn-flat',
                    'data-dismiss' => 'modal', 'aria-label' => 'Close'
                ]) ?>
            </div>
       </div>
    </div>

</div>

<?php
$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$('#build-course-form').submit();return;
        $.post("../course-user/update?id=$model->id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                $("#help_man").load("../course-user/index?course_id=$model->course_id");
                $("#act_log").load("../course-actlog/index?course_id=$model->course_id");
            }
            $.notify({
                message: rel['message'],
            },{
                type: rel['code'] == '200' ? "success " : "danger",
            });
        });
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>