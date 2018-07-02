<?php

use common\models\vk\CustomerAdmin;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CustomerAdmin */

$this->title = Yii::t(null, "{Delete}{Administrators}：{$model->user->nickname}", [
    'Delete' => Yii::t('app', 'Delete'),
    'Administrators' => Yii::t('app', 'Administrators')
]);

?>
<div class="customer-delete-admin main vk-modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'options'=>[
                        'id' => 'form-admin',
                        'class'=>'form-horizontal',
                    ],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-12 col-md-12\">{error}</div>",  
                        'labelOptions' => [
                            'class' => 'col-lg-12 col-md-12',
                        ],  
                    ], 
                ]); ?>
                
                <?= Html::activeHiddenInput($model, 'id') ?>

                <?= Html::encode('确定要删除该管理员？') ?>

                <?php ActiveForm::end(); ?>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary',
                    'data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>

</div>

<?php
$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        $.post("../default/delete-admin?id={$model->id}",$('#form-admin').serialize(),function(data){
            if(data['code'] == '200'){
                var num = Number($("#number").text()) - 1;
                $("#number").html(num);
                $("#admin_info").load("../default/admin-index?id={$model->customer_id}");
            }
        });
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
