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
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Customer'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="customer-create-admin customer">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body customer-activity">
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
                
                <?= Html::activeHiddenInput($model, 'customer_id') ?>
                
                <?= $form->field($model, 'user_id')->widget(Select2::classname(), [
                    'data' => $admins, 
                    'hideSearch' => true,
                    'options' => [
                        'placeholder' => '请选择...',
                    ],
                ])->label(Yii::t('app', '{Add}{People}',['Add'=>Yii::t('app', 'Add'),'People'=> Yii::t('app', 'People')])) ?>

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

$admin = Url::to(['admin-index', 'id' => $model->customer_id]);
$adminUrl = Url::to(['create-admin', 'id' => $model->customer_id]);

$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$("#form-admin").submit();return;
        $.post("$adminUrl",$('#form-admin').serialize(),function(data){
            if(data['code'] == '200'){
                $("#admin").load("$admin");
            }
        });
    });   
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>
