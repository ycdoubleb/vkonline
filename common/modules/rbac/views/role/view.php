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

$this->title = Yii::t(null, '{Role}{Detail}：{Name}', [
            'Role' => Yii::t('app/rbac', 'Role'),
            'Detail' => Yii::t('app', 'Detail'),
            'Name' => $model->name,
        ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/rbac', 'Role'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$group_permissions = ArrayHelper::map($childs, 'name', 'des', 'group_name');
?>

<div class="role-view rbac">

    <p><?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->name], ['class' => 'btn btn-primary']) ?></p>

    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'authgroup.name',
            'name',
            'description:ntext',
        ],
    ])
    ?>
    <div style="width: 100%;position: relative;">
        <div class="rbac-frame child" style="width: 70%;margin-right: 10px">
            <div class="frame-title">
                拥有的权限（<?= count($childs) ?>个）
            </div>

            <?php
                $form = ActiveForm::begin([
                    'id' => 'assigned-permission-form',
                ]);
            ?>
            <div class="frame-body" style="padding:0px">
                <table class="table table-hover table-striped table-bordered table-form">
                    <thead>
                        <tr>
                            <th width='150px'><?= Yii::t('app/rbac', 'Group ID') ?></th>
                            <th><?= Yii::t('app/rbac', 'Permission') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group_permissions as $groupName => $permissions): ?>
                            <tr>
                                <th class="text-right"><?= $groupName ?> <input type="checkbox" onclick="selectAll('<?= $groupName ?>',this.checked)"></th>
                                <td id="<?= $groupName ?>">
                                    <?php foreach ($permissions as $name => $des): ?>
                                        <div class="group-item">
                                            <input type="checkbox" name="<?= "permissions[$groupName][]" ?>" value="<?= $name ?>">
                                            <span class="priv" id="index-index"><?= Html::a($des, ['/rbac/permission/view','id' => $name]) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="frame-footer">
                <?= Html::a(Yii::t('app/rbac', 'Remove Selected'), ['remove-permission','id'=>$model->name], [
                    'type'          => 'submit',
                    'data-method'   => 'post',
                    'class'         => 'btn btn-danger'
                    ]) ?>
                <?= Html::a(Yii::t('app', 'Add'), ['add-permission', 'id' => $model->name], ['id' => 'btn-add-permission', 'class' => 'btn btn-success']); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>

        <div class="rbac-frame user" style="width: 28%;">

            <div class="frame-title">
                已分配该角色的用户（<?= count($users) ?>个）
            </div>
            
            <?php
                $form = ActiveForm::begin([
                    'id' => 'assigned-permission-form',
                ]);
            ?>
            
            <div class="frame-body" id="userlist">
                <?php foreach ($users as $user): ?>
                <div class="group-item-user">
                    <input type="checkbox" name="users[]" value="<?= $user['user_id'] ?>"/>
                    <span class="priv"><?= $user['nickname'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="frame-footer">
                <?= Html::a(Yii::t('app/rbac', 'Select All'), 'javascript:;', ['onclick'=>'selectAll("userlist",true);', 'style' => 'float: left; margin-right: 15px;']); ?>
                <?= Html::a(Yii::t('app/rbac', 'Select None'), 'javascript:;', ['onclick'=>'selectAll("userlist",false);', 'style' => 'float: left;']); ?>
                <?= Html::a(Yii::t('app/rbac', 'Remove Selected'), ['remove-assignment','id' => $model->name], ['class' => 'btn btn-danger', 'data' => ['method' => 'post']]); ?>
                <?= Html::a(Yii::t('app', 'Add'), ['assignment-user', 'id' => $model->name], ['id' => 'btn-assignment-user', 'class' => 'btn btn-success']); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>


</div>

<div class="rbac-model">
<?= $this->render('/user-role/_form_model') ?>    
</div>

<script type="text/javascript">
    /**
     * 选择全部
     **/
    function selectAll(scope,checked){ 
        if(scope){
            $('#' + scope + ' input').each(function(){
                $(this).prop("checked", checked)
            });
        }else{
            $('input:checkbox').each(function(){
                $(this).prop("checked", checked)
            });
        }
    }
    
    
</script>

<?php
$js = <<<JS
    /**
     * 弹出模块框面板
     */ 
    $('#btn-add-permission').click(loadModel);
    $('#btn-assignment-user').click(loadModel);
    
    /**
     * 弹出模态框
     */     
    function loadModel(){
        $(".myModal").html("");
        $('.myModal').modal("show").load($(this).attr("href"));
        return false;
    }    
    
JS;
$this->registerJs($js, View::POS_READY);
?>

<?php
RbacAsset::register($this);
?>
