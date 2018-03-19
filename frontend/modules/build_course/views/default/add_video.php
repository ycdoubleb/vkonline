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
                    'id'=>'submitsave','class'=>'btn btn-primary','data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>
</div>

<?php

$actLog = Url::to(['actlog', 'course_id' => $model->courseNode->course_id]);
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"),"", 
    $this->renderFile('@frontend/modules/build_course/views/default/view_videoframe.php')));
$js = 
<<<JS
            
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$('#build-course-form').submit(); return;
        var items = '$domes';    
        $.post("../default/add-video?node_id=$model->node_id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                var dome = renderHtml(items, rel['data']);
                $('#' + rel['data']['node_id'] + ' > div > .sortable').append(dome);
                $('#toggle_' + rel['data']['id']).load('../default/view-video?id=' + rel['data']['id'] + ' #' + rel['data']['id'] + ' > table');
                sortable('.sortable', {
                    forcePlaceholderSize: true,
                    items: 'li',
                    handle: '.fa-arrows'
                });
                $("#act_log").load("$actLog");
            }
        });
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
