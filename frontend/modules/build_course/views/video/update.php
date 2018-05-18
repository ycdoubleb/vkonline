<?php

use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Video */

ModuleAssets::register($this);

$this->title = Yii::t(null, "{Edit}{Video}", [
    'Edit' => Yii::t('app', 'Edit'), 'Video' => Yii::t('app', 'Video')
]);

?>
<div class="video-update main modal">

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
                    'allTeacher' => $allTeacher,
                    'videoFiles' => $videoFiles,
                    'allTags' => $allTags,
                    'tagsSelected' => $tagsSelected,
                ]) ?>
                
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id' => 'submitsave', 'class' => 'btn btn-primary', 
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
        //$("#build-course-form").submit();return;
        if($('#video-name').val() == ''){
            $('.field-video-name').addClass('has-error');
            $('.field-video-name .help-block').html('视频名称不能为空。');
            return;
        }
        if($('#video-teacher_id').val() == ''){
            $('.field-video-teacher_id').addClass('has-error');
            $('.field-video-teacher_id .help-block').html('主讲老师不能为空。');
            return;
        }
        if((tijiao() && isExist()) == false){
            $('.field-video-source_id').addClass('has-error');
            $('.field-video-source_id .help-block').html('视频文件不能为空或者必须是已上传。');
            setTimeout(function(){
                $('.field-video-source_id').removeClass('has-error');
                $('.field-video-source_id .help-block').html('');
            }, 3000);
            return;
        }
        $.post("../video/update?id=$model->id", $('#build-course-form').serialize(), function(rel){
            if(rel['code'] == '200'){
                $.each(rel['data'],function(key, value){
                    $("#$model->id").find(' > div.head span.'+ key).html(value)
                });
                $("#act_log").load("../course-actlog/index?course_id={$model->courseNode->course_id}");
            }
        });
        $('.myModal').modal('hide');
    });  
 
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>