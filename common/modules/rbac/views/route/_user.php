<?php

use kartik\widgets\Select2;
use common\modules\rbac\models\AuthItem;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model AuthItem */
/* @var $form ActiveForm */

$this->title = '添加用户';
$this->params['breadcrumbs'][] = ['label' => 'Auth Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="role-manager-user rbac">

    <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="modal-body">
            
            <?php $form = ActiveForm::begin([
                'id' => 'assignment-user-form',
            ]); ?>
                
            <?php foreach($users as $userItem): ?>

            <p>
                <?= Html::checkbox('user_id[]', '', ['value' => $userItem['id']]) ?><?= $userItem['nickname'] ?>
            </p>

            <?php endforeach; ?>
                
            <?php ActiveForm::end(); ?>

        </div>
        <div class="modal-footer">
            <?= Html::a('全选', 'javascript:;', ['id' => 'user-role-create-selectAll', 'style' => 'float: left; margin-right: 15px;']); ?>
            <?= Html::a('全不选', 'javascript:;', ['id' => 'user-role-create-unSelect', 'style' => 'float: left;']); ?>
            <button class="btn btn-danger" data-dismiss="modal" aria-label="Close">关闭</button>
            <button id="submit-create-save" class="btn btn-primary">确认</button>
        </div>
   </div>
</div> 

<script type="text/javascript">
    /** 承接操作 提交表单 */
    $("#submit-create-save").click(function()
    {
        $('#assignment-user-form').submit();
    });
</script>

</div>

<?php
   
$js = 
<<<JS
        
    //全选
    $("#user-role-create-selectAll").click(function(){
        $("input[name='user_id[]']:checkbox").each(function(){
            $(this).prop("checked",true);
        });
    });
    //全不选
    $("#user-role-create-unSelect").click(function(){
        $("input[name='user_id[]']:checkbox").each(function(){
            $(this).prop("checked",false);
        });
    });
        
JS;
    $this->registerJs($js, View::POS_READY);
?>