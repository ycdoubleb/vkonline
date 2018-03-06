<?php

use common\modules\rbac\models\AuthItem;
use common\modules\rbac\RbacAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model AuthItem */

$this->title = '角色详情：'.$model->name;
$this->params['breadcrumbs'][] = ['label' => '用户角色', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$roleCategoryItem = ArrayHelper::map($roleCategorys, 'id', 'name');
?>

<div class="role-manager-view rbac">

    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::a('更新', ['update', 'name' => $model->name], ['class' => 'btn btn-primary']) ?></p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'system_id',
                'value' => isset($roleCategoryItem[$model->system_id]) ? $roleCategoryItem[$model->system_id] : null,
            ],
            'name',
            'description:ntext',
        ],
    ]) ?>
    
    <div class="rbac-frame" style="width: 45%; float: left;">
        
        <div class="rbac-number">
            拥有的角色或权限（<?= count($permissions) + ($childRoles[$model->name]->name != $model->name? count($childRoles) : 0)?>个）
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'role-manager-delete-form',
            'action' => '/rbac/role-manager/delete?name='.$model->name
        ]); ?>

        <div class="rbac-delete-form">
            <?php if(!empty($childRoles) || !empty($permissions)): ?>
                <?php foreach($roleCategorys as $roleCategory): ?>
                <p><b><?= $roleCategory['name']; ?></b></p>
                    
                    <?php foreach($childRoles as $childRoleItems): ?>
                        <?php if($childRoleItems->name != $model->name): ?>
                            <?php if($childRoleItems->system_id == $roleCategory['id']): ?>
                            <p style="padding-left: 20px;">
                                <?= Html::checkbox('child[]', '', ['value' => $childRoleItems->name]) ?><?= $childRoleItems->description ?>
                                <span class="prompt">（角色）</span>
                            </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                        
                    <?php foreach($permissions as $permissionItems): ?>
                        <?php if($permissionItems->system_id == $roleCategory['id']): ?>
                        <p style="padding-left: 20px;">
                            <?= Html::checkbox('child[]', '', ['value' => $permissionItems->name]) ?><?= $permissionItems->description ?>
                            <span class="prompt">（权限）</span>
                        </p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                        
                <?php endforeach;?>
            <?php endif; ?>
            
        </div>

        <?php ActiveForm::end(); ?>

        <div class="rbac-btn">
            <?= Html::a('全选', 'javascript:;', ['id' => 'role-manager-selectAll', 'style' => 'float: left; margin-right: 15px;']); ?>
            <?= Html::a('全不选', 'javascript:;', ['id' => 'role-manager-unSelect', 'style' => 'float: left;']); ?>
            <?= Html::a('余除已选', 'javascript:;', ['id' => 'role-manager-delete', 'class' => 'btn btn-danger', 'data' => ['method' => 'post']]); ?>
            <?= Html::a('添加', ['assignment-permission', 'name' => $model->name], ['id' => 'role-manager-create', 'class' => 'btn btn-success']); ?>
        </div>
    </div>
    
    <div class="rbac-frame" style="width: 45%; float: right;">
        
        <div class="rbac-number">
            已分配该角色的用户（<?= count($users)?>个）
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'user-role-delete-form',
            'action' => '/rbac/user-role/delete?item_name='.$model->name.'&is_rbacName=true'
        ]); ?>

        <div class="rbac-delete-form">
            
            <?php if(!empty($users)): ?>
                <?php foreach($users as $userItems): ?>
                <p style="padding-left: 20px;">
                    <?= Html::checkbox('user_id[]', '', ['value' => $userItems['id']]) ?><?= $userItems['nickname'] ?>
                </p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php ActiveForm::end(); ?>

        <div class="rbac-btn">
            <?= Html::a('全选', 'javascript:;', ['id' => 'user-role-selectAll', 'style' => 'float: left; margin-right: 15px;']); ?>
            <?= Html::a('全不选', 'javascript:;', ['id' => 'user-role-unSelect', 'style' => 'float: left;']); ?>
            <?= Html::a('余除已选择用户', 'javascript:;', ['id' => 'user-role-delete', 'class' => 'btn btn-danger', 'data' => ['method' => 'post']]); ?>
            <?= Html::a('添加', ['assignment-user', 'name' => $model->name], ['id' => 'user-role-create', 'class' => 'btn btn-success']); ?>
        </div>
    </div>
    
</div>

<div class="rbac-model">
    <?= $this->render('/user-role/_form_model')?>    
</div>

<?php
$js = 
<<<JS
        
    /** 角色管理删除操作 提交表单 */
    $('#role-manager-delete').click(function()
    {       
        $('#role-manager-delete-form').submit();
    });   
    /** 用户角色删除操作 提交表单 */
    $('#user-role-delete').click(function()
    {       
        $('#user-role-delete-form').submit();
    });   
    /** 角色管理添加操作 提交表单 */
    $('#role-manager-create').click(function()
    {       
        $(".myModal").html("");
        $('.myModal').modal("show").load($(this).attr("href"));
        return false;
    });
    /** 用户角色添加操作 提交表单 */
    $('#user-role-create').click(function()
    {       
        $(".myModal").html("");
        $('.myModal').modal("show").load($(this).attr("href"));
        return false;
    });
    //角色管理操作 全选
    $("#role-manager-selectAll").click(function(){
        $("input[name='child[]']:checkbox").each(function(){
            $(this).prop("checked",true);
        });
    });
    //角色管理操作 全不选
    $("#role-manager-unSelect").click(function(){
        $("input[name='child[]']:checkbox").each(function(){
            $(this).prop("checked",false);
        });
    });
    //用户角色操作 全选
    $("#user-role-selectAll").click(function(){
        $("input[name='user_id[]']:checkbox").each(function(){
            $(this).prop("checked",true);
        });
    });
    //用户角色操作 全不选
    $("#user-role-unSelect").click(function(){
        $("input[name='user_id[]']:checkbox").each(function(){
            $(this).prop("checked",false);
        });
    });
        
JS;
    $this->registerJs($js, View::POS_READY);
?>

<?php
    RbacAsset::register($this);
?>
