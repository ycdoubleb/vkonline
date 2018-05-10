<?php

use common\models\vk\TeacherCertificate;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;


/* @var $this View */
/* @var $model TeacherCertificate */

$this->title = Yii::t('app', 'Verifier');

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
                                
                <?= Html::activeHiddenInput($model, 'verifier_id', ['value' => Yii::$app->user->id]) ?>
                
                <?= Html::activeHiddenInput($model, 'verifier_at', ['value' => time()]) ?>
                
                <?= Html::activeHiddenInput($model, 'is_dispose', ['value' => 1]) ?>
                
                <?= $form->field($model, 'is_pass')->radioList(TeacherCertificate::$passStatus)
                        ->label(Yii::t('app', 'Name'))?>
                
                <?= $form->field($model, 'feedback')->textarea(['rows' => 5])
                         ->label(Yii::t('app', 'Remarks'))?>
                
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

$adminUrl = Url::to(['verifier', 'id' => $model->id]);

$js = 
<<<JS
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        $.post("$adminUrl",$('#form-admin').serialize(),function(data){
            if(data['code'] == '200'){
                window.location.reload();
            }
        });
    });   
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>
