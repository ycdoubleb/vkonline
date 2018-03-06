<?php

use common\modules\rbac\models\AuthItem;
use common\modules\rbac\RbacAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model AuthItem */
$this->title = Yii::t(null,'{Permission}{Detail}：{Name}', [
    'Detail' => Yii::t('app', 'Detail'),
    'Permission' => Yii::t('app/rbac', 'Permission'),
    'Name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/rbac', 'Permission'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$parentGroups = ArrayHelper::map($parents, 'name', 'des', 'group_name');
$childGroups = ArrayHelper::map($childs, 'name', 'des', 'group_name');
$userGroups = ArrayHelper::map($users, 'user_id', 'nickname', 'item_name');
?>
<div class="permission-view rbac">

    <p><?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->name], ['class' => 'btn btn-primary']) ?></p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'authgroup.name',
            'name',
            'description:ntext',
        ],
    ]) ?>
    
    <div class="rbac-frame child">
        <div class="frame-title">
            包括的路由（<span class="count"></span>个）
        </div>
        <div class="frame-body" style="padding: 0px">
            <select multiple size="20" class="form-control list" data-target="available"></select>
        </div>
        <div class="frame-footer">
            <?= Html::a(Yii::t('app/rbac', 'Remove Selected'),['remove','id'=>$model->name], ['id' => 'btn-remove', 'class' => 'btn btn-danger']); ?>
            <?= Html::a(Yii::t('app', 'Add'), ['add-route', 'name' => $model->name], ['id' => 'btn-assign', 'class' => 'btn btn-success']); ?>
        </div>
    </div>
    
    <div style="width: 100%;margin-top: 20px;">
        <div class="rbac-frame" style="width: 49%;margin-right: 10px;">

            <div class="frame-title">
                拥有该权限的角色（<span class="count"><?= count($parents) ?></span>个）
            </div>

            <div class="frame-body">
                <?php foreach ($parentGroups as $name => $parentGroup): ?>
                <div style="margin-bottom: 10px">
                    <p style="margin-bottom: 0px"><b><?= $name; ?></b></p>

                    <?php foreach ($parentGroup as $itemName): ?>
                        <p style="padding-left: 20px; text-align: left;">
                            <?= $itemName ?>
                            <span class="prompt">（角色）</span>
                        </p>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>

            </div>

        </div>    

        <div class="rbac-frame" style="width: 49%;">

            <div class="frame-title">
                拥有该权限的用户（<span class="count"><?= count($users) ?></span>个）
            </div>

            <div class="frame-body">
                <?php foreach ($userGroups as $name => $userGroup): ?>
                <div style="margin-bottom: 10px">
                    <p style="margin-bottom: 0px"><b><?= $name; ?></b><span class="prompt">（角色）</span></p>

                    <?php foreach ($userGroup as $user_id => $nickname): ?>
                        <span style="padding-left: 20px; text-align: left; display: inline-block">
                            <?= $nickname ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>

            </div>

        </div> 
    </div>
</div>

<div class="rbac-model">
    <?= $this->render('/user-role/_form_model')?>    
</div>

<?php
$var_childs = Json::encode(ArrayHelper::getColumn($childs, 'name'));
$js = 
<<<JS
    /**
     * 删除已经选择路径
     * 返回结果得新刷新路径列表   
     */
    $('#btn-remove').click(function(){
        var items = $('.child').find('.frame-body').find('.list').val();
        $.post($(this).attr("href"),{'items':items},function(r){
            childs = [];
            $.each(r.assigned,function(index,item){
                childs.push(index);
            });
            renderChild();
        });
        return false;
    });
    /**
     * 弹出路由添加面板
     */ 
    $('#btn-assign').click(loadModel);
    
    function loadModel(){
        $(".myModal").html("");
        $('.myModal').modal("show").load($(this).attr("href"));
        return false;
    }
        
    /**
     * 刷新路由列表
     */
    var childs = $var_childs;
    function renderChild(){
        $('.child').find('.count').html(childs.length);
        var list = $('.child').find('.frame-body').find('.list');
        list.empty();
        $.each(childs,function(){
            $('<option>').val(this).text(this).appendTo(list);
        })
    }
    renderChild();
JS;
    $this->registerJs($js, View::POS_READY);
?>

<?php
    RbacAsset::register($this);
?>