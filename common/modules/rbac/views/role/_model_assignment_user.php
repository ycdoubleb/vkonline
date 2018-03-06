<?php

use kartik\select2\Select2;
use common\modules\rbac\models\AuthItem;
use common\modules\rbac\RbacAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model AuthItem */
/* @var $form ActiveForm */
$this->title = Yii::t('app', 'Add') . Yii::t('app', 'User');
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>

<div class="model-assignment_user rbac">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <?php
                $form = ActiveForm::begin([
                    'id' => 'assignment-user-form',
                ]);?>
            <div class="modal-body">
                <?= Select2::widget([
                    'id' => 'users',
                    'name' => 'users',
                    'value' => null, // initial value
                    'data' => $available,
                    'maintainOrder' => false,
                    'options' => ['placeholder' =>  Yii::t('app', 'Select Placeholder'), 'multiple' => true],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]) ?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" data-dismiss="modal" aria-label="Close"><?= Yii::t('app', 'Close') ?></button>
                <?= Html::a(Yii::t('app', 'Submit'), ['assignment-user','id'=>$id], [
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
    
JS;
$this->registerJs($js, View::POS_READY);
RbacAsset::register($this);
?>