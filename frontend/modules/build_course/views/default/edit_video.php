<?php

use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Video */

ModuleAssets::register($this);

$this->title = Yii::t(null, "{Edit}{Video}：{$model->name}", [
    'Edit' => Yii::t('app', 'Edit'), 'Video' => Yii::t('app', 'Video')
]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="video-update main modal">

    <div class="modal-dialog modal-lg" style="width: 1100px" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body height">
                
                <?= $this->render('_form_video', [
                    'model' => $model,
                    'allRef' => $allRef,
                    'allTeacher' => $allTeacher,
                    'videoFile' => $videoFile,
                    'attFiles' => $attFiles,
                ]) ?>
                
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary','data-dismiss'=>'','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>

</div>

<?php

$actLog = Url::to(['actlog', 'course_id' => $model->courseNode->course_id]);
$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$("#build-course-form").submit();return;
        if($('#video-name').val() == ''){
            return;
        }
        if($('#video-teacher_id').val() == ''){
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
        $.post("../default/edit-video?id=$model->id", $('#build-course-form').serialize(), function(rel){
            if(rel['code'] == '200'){
                $.each(rel['data'],function(key, value){
                    $("#$model->id").find('> div.head.gray span.'+ key).html(value);
                    $("#$model->id").find('> div.head.gray i.'+ key).css({'display': value});
                    if(key == 'id'){
                        $('#toggle_' + value).load('../default/view-video?' + key + '=' + value + ' #' + value + ' > table');
                    }
                });
                $("#act_log").load("$actLog");
            }
        });
        $('.myModal').modal('hide');
    });  
 
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>