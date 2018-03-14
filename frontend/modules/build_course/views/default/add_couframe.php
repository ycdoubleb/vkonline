<?php

use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */


ModuleAssets::register($this);

$this->title = Yii::t('app', "{Add}{Node}",[
    'Add' => Yii::t('app', 'Add'), 'Node' => Yii::t('app', 'Node')
]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="course_frame-create main modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                
                <?= $this->render('_form_frame', [
                    'model' => $model,
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

$actLog = Url::to(['actlog', 'course_id' => $course_id]);
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"),"", 
    $this->renderFile('@frontend/modules/build_course/views/default/view_couframe.php')));
$js = 
<<<JS
            
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$('#build-course-form').submit(); return;
        var items = '$domes';    
        $.post("../default/add-couframe?course_id=$course_id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                var dome = renderHtml(items, rel['data']);
                $(".sortable").eq(0).append(dome);
//                if(data['data']['parent_id'] == ''){
//                    $(".sortable").eq(0).append(dome);
//                }else{
//                    $("#"+data['data']['parent_id']+">div >.sortable").append(dome);
//                }
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