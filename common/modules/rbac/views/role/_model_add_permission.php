<?php

use common\modules\rbac\models\AuthItem;
use common\modules\rbac\RbacAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model AuthItem */
/* @var $form ActiveForm */
$this->title = Yii::t('app', 'Add') . Yii::t('app/rbac', 'Permission');
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
$available = ArrayHelper::map($available, 'name', 'des', 'group_name');
?>

<div class="model-add-permission rbac">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <?php
                $form = ActiveForm::begin([
                    'id' => 'assignment-permission-form',
                ]);
            ?>
            <div class="modal-body">
                <table class="table table-hover table-striped table-bordered table-form">
                    <thead>
                        <tr>
                            <th width='150px'><?= Yii::t('app/rbac', 'Group ID') ?></th>
                            <th><?= Yii::t('app/rbac', 'Permission') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available as $groupName => $permissions): ?>
                            <tr>
                                <th class="text-right"><?= $groupName ?> <input type="checkbox" onclick="selectAll('<?= $groupName ?>',this.checked )"></th>
                                <td id="<?= $groupName ?>">
                                    <?php foreach ($permissions as $name => $des): ?>
                                        <div class="group-item">
                                            <input type="checkbox" name="<?= "permissions[$groupName][]" ?>" value="<?= $name ?>">
                                            <span class="priv" id="index-index"><?= $des ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" data-dismiss="modal" aria-label="Close"><?= Yii::t('app', 'Close') ?></button>
                <?= Html::a(Yii::t('app', 'Submit'), ['add-permission','id'=>$id], [
                    'type'          => 'submit',
                    'data-method'   => 'post',
                    'class'         => 'btn btn-primary'
                    ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div> 
</div>

<?php
$js = <<<JS
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
JS;
$this->registerJs($js, View::POS_READY);
RbacAsset::register($this);
?>