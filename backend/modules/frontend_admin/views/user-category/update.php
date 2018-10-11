<?php

use common\models\vk\UserCategory;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model UserCategory */

$this->title = Yii::t('app', "{Update}{Catalog}：{$model->name}",[
    'Update' => Yii::t('app', 'Update'),
    'Catalog' => Yii::t('app', 'Catalog'),
]);

?>

<div class="user-category-update">

    <div class="modal-dialog" style="width: 720px" role="document">
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
                    'id' => 'submitsave', 'class' => 'btn btn-primary btn-flat',
                    'data-dismiss' => 'modal', 'aria-label' => 'Close'
                ]) ?>
            </div>
            
        </div>
    </div>

</div>