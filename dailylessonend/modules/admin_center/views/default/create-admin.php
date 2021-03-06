<?php

use common\models\vk\CustomerAdmin;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;


/* @var $this View */
/* @var $model CustomerAdmin */

$this->title = Yii::t('app', '{Add}{Administrators}',[
    'Add' => Yii::t('app', 'Add'),
    'Administrators' => Yii::t('app', 'Administrators')
]);

?>

<div class="customer-create-admin main vk-modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            
            <div class="modal-body customer-activity">
                <div class="vk-form clear-shadow">
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
                
                <!--客户id-->    
                <?= Html::activeHiddenInput($model, 'customer_id') ?>
                
                <!--用户-->
                <?= $form->field($model, 'user_id')->widget(Select2::classname(), [
                    'data' => $admins, 
                    'hideSearch' => true,
                    'options' => [
                        'placeholder' => '请选择...',
                    ],
                ])->label(Yii::t('app', '{Add}{People}',['Add'=>Yii::t('app', 'Add'),'People'=> Yii::t('app', 'People')])) ?>

                <!--权限-->
                <?= $form->field($model, 'level', [
                    'template' => "{label}\n<div class=\"col-lg-4 col-md-4\">{input}</div>\n<div class=\"col-lg-4 col-md-4\">{error}</div>",
                    'labelOptions' => [
                        'class' => 'col-lg-12 col-md-12',
                    ],
                ])->widget(Select2::classname(), [
                    'data' => CustomerAdmin::$levelName, 
                    'hideSearch' => true,
                    'options' => [
                        'placeholder' => '请选择...'
                    ]
                ])->label(Yii::t('app', '{Set}{Privilege}',['Set'=> Yii::t('app', 'Set'),'Privilege'=> Yii::t('app', 'Privilege')])) ?>

                <?php ActiveForm::end(); ?>
                </div>
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
$js = <<<JS
    //提交表单
    $("#submitsave").click(function(){
        $.post("../default/create-admin?id={$model->customer_id}", $('#form-admin').serialize(),function(data){
            if(data['code'] == '200'){
                var num = Number($("#number").text()) + 1;
                $("#number").html(num);
                $("#admin_info").load("../default/admin-index?id={$model->customer_id}");
            }
        });
    });   
JS;
    $this->registerJs($js,  View::POS_READY);
?>
