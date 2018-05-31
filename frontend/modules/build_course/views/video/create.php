<?php

use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Video */


ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t('app', "{Add}{Video}",[
    'Add' => Yii::t('app', 'Add'), 'Video' => Yii::t('app', 'Video')
]);

?>

<div class="video-create main modal">

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
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/_node.php')));
$js = 
<<<JS

    // 提交表单
    $("#submitsave").click(function(){
        //是否为外链
        var is_link = $("#video-source_is_link").val();
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
        if(is_link == 0 || is_link == undefined){
            if(!(tijiao() && isExist())){
                $('.field-video-source_id').addClass('has-error');
                $('.field-video-source_id .help-block').html('视频文件不能为空或者必须是已上传。');
                setTimeout(function(){
                    $('.field-video-source_id').removeClass('has-error');
                    $('.field-video-source_id .help-block').html('');
                }, 3000);
                return;
            }
        }else{
            if(isExist()){
                $('.field-outside_link').addClass('has-error');
                $('.field-outside_link .help-block').html('请先删除视频文件，再添加外部链接。');
                setTimeout(function(){
                    $('.field-outside_link').removeClass('has-error');
                    $('.field-outside_link .help-block').html('');
                }, 3000);
                return;
            }
        }
        
        var items = $domes;    
        $.post("../video/create?node_id=$model->node_id", $('#build-course-form').serialize(), function(rel){
            if(rel['code'] == '200'){
                var dome = Wskeee.StringUtil.renderDOM(items, rel['data']);
                $('#' + rel['data']['node_id'] + ' > div > .sortable').append(dome);
                sortable('.sortable', {
                    forcePlaceholderSize: true,
                    items: 'li',
                    handle: '.fa-arrows'
                });
                $("#act_log").load("../course-actlog/index?course_id={$model->courseNode->course_id}");
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
