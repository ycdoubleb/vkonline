<?php

use common\models\vk\Knowledge;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Knowledge */

ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t(null, "{Edit}{Knowledge}", [
    'Edit' => Yii::t('app', 'Edit'), 'Knowledge' => Yii::t('app', 'Knowledge')
]);


?>
<div class="knowledge-update main modal">

    <div class="modal-dialog modal-lg modal-width" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body modal-height">
                
                <?= $this->render('_form', [
                    'model' => $model,
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
$js = 
<<<JS
        
    // 提交表单
    $("#submitsave").click(function(){
        if($('#knowledge-name').val() == ''){
            $('.field-video-name').addClass('has-error');
            $('.field-video-name .help-block').html('名称不能为空。');
            return;
        }
        $.post("../knowledge/update?id=$model->id", $('#build-course-form').serialize(), function(rel){
            if(rel['code'] == '200'){
                $.each(rel['data'],function(key, value){
                    $("#$model->id").find(' > div.head span.'+ key).html(value);
                });
                $("#act_log").load("../course-actlog/index?course_id={$model->node->course_id}");
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
