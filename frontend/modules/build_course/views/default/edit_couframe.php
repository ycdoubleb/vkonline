<?php

use mconline\modules\mcbs\assets\McbsAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */

$is_show = null;
if(Yii::$app->controller->action->id == 'update-couphase')
    $is_show = "（{$model->value_percent}分）";

$this->title = Yii::t(null, "{edit}{$title}：{$model->name}{$is_show}", [
    'edit' => Yii::t('app', 'Edit'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="mcbs-update-couframe mcbs">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                <?= $this->render('couframe_form',[
                    'model' => $model,
                    'is_show' => $is_show
                ]) ?>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary',
                    'data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>

</div>

<?php

$action = Url::to(['course-make/'.Yii::$app->controller->action->id, 'id' => $model->id]);
$actlog = Url::to(['course-make/log-index', 'course_id' => $course_id]);

$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$("#form-couframe").submit();return;
        $.post("$action",$('#form-couframe').serialize(),function(data){
            if(data['code'] == '200'){
                $.each(data['data'],function(key,value){
                    $("#$model->id").find('> div.head span.'+key).html(value);
                    $("#$model->id").find('> div.collapse p.'+key).html(value);
                });
                $("#action-log").load("$actlog");
            }
        });
    });  
 
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>

<?php
    McbsAssets::register($this);
?>