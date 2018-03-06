<?php

use common\modules\rbac\models\AuthItem;
use common\modules\rbac\RbacAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $roleItems AuthItem */

$this->title = Yii::t(null, '{User}{Detail}：{Name}', [
            'User' => Yii::t('app', 'User'),
            'Detail' => Yii::t('app', 'Detail'),
            'Name' => $model->nickname]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User') . Yii::t('app/rbac', 'Role'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$roles = ArrayHelper::map($roles, 'name', 'description');
?>

<div class="user-role-view rbac">

    <div class="rbac-frame">

        <div class="frame-title">
            已分配角色（<?= count($roles) ?>个）
        </div>

        <?php
        $form = ActiveForm::begin([
                    'id' => 'user-role-remove-form',
        ]);?>

        <div class="frame-body" id="rolelist">
        <?php foreach ($roles as $name => $description): ?>
            <div>
                <input type="checkbox" name="roles[]" value="<?= $name ?>"/>
                <span class="priv"><?= $description ?></span>
            </div>
        <?php endforeach; ?>           
        </div>
        
        <div class="frame-footer">
            <?= Html::a(Yii::t('app/rbac', 'Select All'), 'javascript:;', ['onclick' => 'selectAll("rolelist",true);', 'style' => 'float: left; margin-right: 15px;']); ?>
            <?= Html::a(Yii::t('app/rbac', 'Select None'), 'javascript:;', ['onclick' => 'selectAll("rolelist",false);', 'style' => 'float: left;']); ?>
            <?=
            Html::a(Yii::t('app/rbac', 'Remove Selected'), ['remove', 'user_id' => $model->id], [
                'type' => 'submit',
                'data-method' => 'post',
                'class' => 'btn btn-danger'
            ])
            ?>
            <?= Html::a(Yii::t('app', 'Add'), ['add-role', 'user_id' => $model->id], ['id' => 'btn-add-role', 'class' => 'btn btn-success']); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

</div>

<div class="rbac-model">
<?= $this->render('_form_model') ?>    
</div>

<script type="text/javascript">
    /**
    * 选择全部
    **/
    function selectAll(scope, checked) {
        if (scope) {
            $('#' + scope + ' input').each(function () {
                $(this).prop("checked", checked)
            });
        } else {
            $('input:checkbox').each(function () {
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
    $('#btn-add-role').click(loadModel);
    
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
