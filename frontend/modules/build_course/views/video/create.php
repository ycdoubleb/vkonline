<?php

use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Video */


ModuleAssets::register($this);

$this->title = Yii::t('app', "{Add}{Video}",[
    'Add' => Yii::t('app', 'Add'), 'Video' => Yii::t('app', 'Video')
]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="video-create main modal">

    <div class="modal-dialog modal-lg" style="width: 1000px" role="document">
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
                    'allRef' => $allRef,
                    'allTeacher' => $allTeacher,
                    'videoFiles' => $videoFiles,
                    'allTags' => $allTags,
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
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/_node.php')));
$js = 
<<<JS
    window.onloadUploader();    //加载文件上传  
    // 提交表单
    $("#submitsave").click(function(){
        //$('#build-course-form').submit(); return;
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
        var items = $domes;    
        $.post("../video/create?node_id=$model->node_id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                var dome = renderHtml(items, rel['data']);
                $('#' + rel['data']['node_id'] + ' > div > .sortable').append(dome);
                sortable('.sortable', {
                    forcePlaceholderSize: true,
                    items: 'li',
                    handle: '.fa-arrows'
                });
                $("#act_log").load("../course-actlog/index?course_id={$model->courseNode->course_id}");
            }
        });
        $('.myModal').modal('hide');
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
