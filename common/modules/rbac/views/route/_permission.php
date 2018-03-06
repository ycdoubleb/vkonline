<?php

use kartik\widgets\Select2;
use common\modules\rbac\models\AuthItem;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model AuthItem */
/* @var $form ActiveForm */
$this->title = '添加权限';
$this->params['breadcrumbs'][] = ['label' => 'Auth Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="role-manager-permission rbac">

    <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="modal-body">
            
            <?php $form = ActiveForm::begin([
                'id' => 'assignment-permission-form',
            ]); ?>
            
            <?php foreach($roleCategorys as $roleCategory): ?>
            <p><b><?= $roleCategory['name']; ?></b></p>
                <?php foreach($permissions as $permItem): ?>
                    <?php if($permItem->system_id == $roleCategory['id']): ?>
                    <p style="padding-left: 20px;">
                        <?= Html::checkbox('child[]', '', ['value' => $permItem->name]) ?><?= $permItem->description ?>
                    </p>
                    <?php endif; ?>
                <?php endforeach; ?>
                
            <?php endforeach; ?>
                
            <?php ActiveForm::end(); ?>

        </div>
        <div class="modal-footer">
            <?= Html::a('全选', 'javascript:;', ['id' => 'role-manager-create-selectAll', 'style' => 'float: left; margin-right: 15px;']); ?>
            <?= Html::a('全不选', 'javascript:;', ['id' => 'role-manager-create-unSelect', 'style' => 'float: left;']); ?>
            <button class="btn btn-danger" data-dismiss="modal" aria-label="Close">关闭</button>
            <button id="submit-create-save" class="btn btn-primary">确认</button>
        </div>
   </div>
</div> 

<script type="text/javascript">
    /** 承接操作 提交表单 */
    $("#submit-create-save").click(function()
    {
        $('#assignment-permission-form').submit();
    });
</script>

</div>

<?php
   
$js = 
<<<JS
        
    //全选
    $("#role-manager-create-selectAll").click(function(){
        $("input[name='child[]']:checkbox").each(function(){
            $(this).prop("checked",true);
        });
    });
    //全不选
    $("#role-manager-create-unSelect").click(function(){
        $("input[name='child[]']:checkbox").each(function(){
            $(this).prop("checked",false);
        });
    });
        
JS;
    $this->registerJs($js, View::POS_READY);
?>