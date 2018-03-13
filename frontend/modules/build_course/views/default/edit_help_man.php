<?php

use common\models\vk\CourseUser;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CourseUser */

ModuleAssets::register($this);

$this->title = Yii::t(null, "{Edit}{HelpMan}：{$model->user->nickname}", [
    'Edit' => Yii::t('app', 'Edit'), 'HelpMan' => Yii::t('app', 'Help Man')
]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="help_man-update main modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'options'=>['id' => 'build-course-form','class'=>'form-horizontal',],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-12 col-md-12\">{error}</div>",  
                        'labelOptions' => [
                            'class' => 'col-lg-12 col-md-12',
                        ],  
                    ], 
                ]); ?>

                <?= $form->field($model, 'privilege', [
                    'template' => "{label}\n<div class=\"col-lg-4 col-md-4\">{input}</div>\n<div class=\"col-lg-4 col-md-4\">{error}</div>",
                    'labelOptions' => [
                        'class' => 'col-lg-12 col-md-12',
                    ],
                ])->widget(Select2::classname(), [
                    'data' => CourseUser::$privilegeMap, 
                    'options' => [
                        'placeholder' => '请选择...'
                    ]
                ])->label(Yii::t(null, '{set}{privilege}',['set'=> Yii::t('app', 'Set'),'privilege'=> Yii::t('app', 'Privilege')])) ?>

                <?php ActiveForm::end(); ?>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary',
                    'data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>

</div>

<?php

$helpMan = Url::to(['helpman', 'course_id' => $model->course_id]);
$helpManUrl = Url::to(['edit-helpman', 'id' => $model->id]);
$actLog = Url::to(['actlog', 'course_id' => $model->course_id]);;

$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$('#build-course-form').submit();return;
        $.post("$helpManUrl",$('#build-course-form').serialize(),function(data){
            if(data['code'] == '200'){
                $("#help_man").load("$helpMan");
                $("#action-log").load("$actLog");
            }
        });
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>