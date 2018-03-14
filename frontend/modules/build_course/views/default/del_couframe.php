<?php

use common\models\vk\CourseNode;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CourseNode */


ModuleAssets::register($this);

$this->title = Yii::t(null, "{Delete}{Node}：{$model->name}", [
    'Delete' => Yii::t('app', 'Delete'), 'Node' => Yii::t('app', 'Node')
]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="course_frame-delete main modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'options'=>['id' => 'build-course-form','class'=>'form-horizontal',],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-12 col-md-12\">{error}</div>",  
                        'labelOptions' => [
                            'class' => 'col-lg-12 col-md-12',
                        ],  
                    ], 
                ]); ?>
                
                <?= Html::activeHiddenInput($model, 'id') ?>
                <?= Html::activeHiddenInput($model, 'is_del', ['value' => 1]) ?>

                <?= Html::encode("确定要删除【{$model->name}】环节？") ?>

                <?php ActiveForm::end(); ?>
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
        $.post("../default/del-couframe?id=$model->id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                $("#$model->id").remove();
                $("#act_log").load("$actLog");
            }
        });
    });  
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>