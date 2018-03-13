<?php

use mconline\modules\mcbs\assets\McbsAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

$is_show = null;
if(Yii::$app->controller->action->id == 'delete-couphase')
    $is_show = "（{$model->value_percent}分）";

$this->title = Yii::t(null, "{delete}{$title}：{$model->name}{$is_show}", [
    'delete' => Yii::t('app', 'Delete'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="mcbs-delete-couframe mcbs">

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
                    'options'=>[
                        'id' => 'form-couframe',
                        'class'=>'form-horizontal',
                    ],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-12 col-md-12\">{error}</div>",  
                        'labelOptions' => [
                            'class' => 'col-lg-12 col-md-12',
                        ],  
                    ], 
                ]); ?>
                
                <?= Html::activeHiddenInput($model, 'id') ?>
                <?= Html::activeHiddenInput($model, 'is_del',['value'=>1]) ?>

                <?= Html::encode("确定要删除该课程{$title}？") ?>

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

$action = Url::to(['course-make/'.Yii::$app->controller->action->id, 'id' => $model->id]);
$actlog = Url::to(['course-make/log-index', 'course_id' => $course_id]);

$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        $.post("$action",$('#form-couframe').serialize(),function(data){
            if(data['code'] == '200'){
                $("#$model->id").remove();
                $("#action-log").load("$actlog");
            }
        });
    });  
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>

<?php
    McbsAssets::register($this);
?>