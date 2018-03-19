<?php

use common\models\vk\CourseNode;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model CourseNode */

ModuleAssets::register($this);

$this->title = Yii::t(null, "{Edit}{Node}：{$model->name}", [
    'Edit' => Yii::t('app', 'Edit'), 'Node' => Yii::t('app', 'Node')
]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
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
                <?= $this->render('_form_frame',[
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

$actLog = Url::to(['actlog', 'course_id' => $model->course_id]);
$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$("#build-course-form").submit();return;
        $.post("../default/edit-couframe?id=$model->id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                $.each(rel['data'],function(key,value){
                    $("#$model->id").find('> div.head span.'+key).html(value);
//                    $("#$model->id").find('> div.collapse p.'+key).html(value);
                });
                $("#act_log").load("$actLog");
            }
        });
    });  
 
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>