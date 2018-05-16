<?php

use common\models\vk\CourseNode;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model CourseNode */


ModuleAssets::register($this);

$this->title = Yii::t('app', "{Add}{Node}",[
    'Add' => Yii::t('app', 'Add'), 'Node' => Yii::t('app', 'Node')
]);

?>

<div class="course-node-create main modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                
                <?= $this->render('_form', [
                    'model' => $model,
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
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/course-node/view.php')));
$js = 
<<<JS
            
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$('#build-course-form').submit(); return;
        if($('#coursenode-name').val() == ''){
            $('.field-coursenode-name').addClass('has-error');
            $('.field-coursenode-name .help-block').html('名称不能为空。');
            return;
        }
        var items = $domes;    
        $.post("../course-node/create?course_id=$model->course_id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                var dome = Wskeee.StringUtil.renderDOM(items, rel['data']);
                $(".sortable").eq(0).append(dome);
                sortable('.sortable', {
                    forcePlaceholderSize: true,
                    items: 'li',
                    handle: '.fa-arrows'
                });
                $("#act_log").load("../course-actlog/index?course_id=$model->course_id");
            }
        });
        $('.myModal').modal('hide');
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>