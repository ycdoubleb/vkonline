<?php

use common\models\vk\CourseNode;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CourseNode */


ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t(null, "{Delete}{Node}：{$model->name}", [
    'Delete' => Yii::t('app', 'Delete'), 'Node' => Yii::t('app', 'Node')
]);

?>
<div class="course-node-delete main modal">

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
                
                <?= Html::activeHiddenInput($model, 'id') ?>

                <?= Html::encode("确定要删除【{$model->name}】环节？") ?>

                <?php ActiveForm::end(); ?>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary btn-flat','data-dismiss'=>'modal','aria-label'=>'Close'
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
        //$('#build-course-form').submit(); return;
        $.post("../course-node/delete?id=$model->id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                $("#$model->id").remove();
                if($("#course_node li").length <= 0){
                    $('<li class="empty"><div class="head"><center>没有找到数据。</center></div></li>').appendTo($("#course_node"));
                }
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