<?php

use common\models\vk\CourseAttachment;
use yii\helpers\Html;
use yii\web\View;


/* @var $this View */
/* @var $model CourseAttachment */

$this->title = Yii::t('app', 'Uploading course resources');

?>
<div class="course-attachment-create  main vk-modal">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            
            <div class="modal-header set-bottom">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            
            <div class="modal-body" style="min-height: 150px;">

                <?= $this->render('_form', [
                    'model' => $model,
                    'attFiles' => [],
                ]) ?>

            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id' => 'submitsave', 'class' => 'btn btn-primary btn-flat',
                    'data-dismiss' => '', 'aria-label' => 'Close'
                ]) ?>
            </div>
            
       </div>
    </div>
    
</div>

<?php 
$js = <<<JS
    //提交表单    
    $("#submitsave").click(function(){
        //判断所有附件是否已经提交
        if(tijiao() == false){
            $(".field-courseattachment-file_id").addClass("has-error");
            $(".field-courseattachment-file_id .help-block").html("文件必须是已上传。");
            setTimeout(function(){
                $(".field-courseattachment-file_id").removeClass("has-error");
                $(".field-courseattachment-file_id .help-block").html("");
            }, 5000);
            return;
        }
        $.post("../course-attachment/create?course_id=$model->course_id", $('#build-course-form').serialize(), function(rel){
            if(rel['code'] == '200'){
                $("#course_attachment").load("../course-attachment/index?course_id={$model->course_id}");
                $("#act_log").load("../course-actlog/index?course_id=$model->course_id");
            }
            $.notify({
                message: rel['message'],
            },{
                type: rel['code'] == '200' ? "success " : "danger",
            });
        });
        $('.myModal').modal('hide');
    });
JS;
    $this->registerJs($js,  View::POS_READY);
?>