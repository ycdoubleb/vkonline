<?php

use common\models\vk\CourseNode;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model CourseNode */

$this->title = Yii::t(null, "{Edit}{Node}", [
    'Edit' => Yii::t('app', 'Edit'), 'Node' => Yii::t('app', 'Node')
]);

?>
<div class="course-node-update main vk-modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                <?= $this->render('_form',[
                    'model' => $model,
                ]) ?>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary btn-flat','data-dismiss'=>'','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>

</div>

<?php
$js = <<<JS
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$("#build-course-form").submit();return;
        if($('#coursenode-name').val() == ''){
            return;
        }
        $.post("../course-node/update?id=$model->id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                $.each(rel['data'],function(key,value){
                    $("#$model->id").find(' > div.head span.'+ key).html(value);
                });
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