<?php

use common\models\vk\Knowledge;
use yii\helpers\Html;
use yii\web\View;


/* @var $this View */
/* @var $model Knowledge */

$this->title = Yii::t('app', "{Add}{Knowledge}",[
    'Add' => Yii::t('app', 'Add'), 'Knowledge' => Yii::t('app', 'Knowledge')
]);

?>

<div class="knowledge-create main vk-modal">

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
//加载 ITEM_DOM 模板
$item_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/knowledge/view.php')));
$js = 
<<<JS

    // 提交表单
    $("#submitsave").click(function(){
        if($('#knowledge-name').val() == ''){
            $('.field-knowledge-name').addClass('has-error');
            $('.field-knowledge-name .help-block').html('名称不能为空。');
            return;
        }
        $.post("../knowledge/create?node_id=$model->node_id", $('#build-course-form').serialize(), function(rel){
            if(rel['code'] == '200'){
                var dome = Wskeee.StringUtil.renderDOM($item_dom, rel['data']);
                $('#' + rel['data']['node_id'] + ' > div > .sortable').append(dome);
                sortable('.sortable', {
                    forcePlaceholderSize: true,
                    items: 'li',
                    handle: '.fa-arrows'
                });
                $("#act_log").load("../course-actlog/index?course_id={$model->node->course_id}");
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
