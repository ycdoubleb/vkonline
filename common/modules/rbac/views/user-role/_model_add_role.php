<?php

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

$this->title = Yii::t('app', 'Add') . Yii::t('app', 'Role');
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>
<div class="user-add-role rbac">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <?php
            $form = ActiveForm::begin([
                        'id' => 'assignment-user-form',
            ]);
            ?>
            <div class="modal-body" id="rolelist">
                <?php foreach ($available as $name => $description): ?>
                <div>
                    <input type="checkbox" name="roles[]" value="<?= $name ?>"/>
                    <span class="priv"><?= $description ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <?= Html::a(Yii::t('app/rbac', 'Select All'), 'javascript:;', ['onclick' => 'selectAll("rolelist",true);', 'style' => 'float: left; margin-right: 15px;']); ?>
                <?= Html::a(Yii::t('app/rbac', 'Select None'), 'javascript:;', ['onclick' => 'selectAll("rolelist",false);', 'style' => 'float: left;']); ?>
                <button class="btn btn-danger" data-dismiss="modal" aria-label="Close"><?= Yii::t('app', 'Close') ?></button>
                <?=
                Html::a(Yii::t('app', 'Submit'), ['add-role', 'user_id' => $id], [
                    'type' => 'submit',
                    'data-method' => 'post',
                    'class' => 'btn btn-primary'
                ])?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div> 
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