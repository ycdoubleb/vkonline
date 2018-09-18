<?php

use common\models\vk\CustomerActLog;
use common\widgets\ueditor\UeditorAsset;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;


/* @var $this View */
/* @var $model CustomerActLog */

$this->title = Yii::t('app', 'Renew');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Customer'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="customer-renew customer">

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
                        'id' => 'form-renew',
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
                                
                <?= $form->field($model, 'good_id', [
                    'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-12 col-md-12',],
                ])->widget(Select2::class, [
                    'data' => $goods, 
                    'hideSearch' => true,
                    'options' => [
                        'placeholder' => Yii::t('app', 'Select Placeholder'),
                    ],
                ])->label(Yii::t('app', 'Good ID')) ?>

                <?= $form->field($model, 'start_time', [
                    'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-12 col-md-12',],
                ])->widget(Select2::class, [
                    'data' => CustomerActLog::$longTime, 
                    'hideSearch' => true,
                    'options' => [
                        'placeholder' => Yii::t('app', 'Select Placeholder'),
                    ],
                ])->label(Yii::t('app', 'Long Time'))?>
                
                <?= $form->field($model, 'content')->textarea([
                    'id' => 'container', 
                    'type' => 'text/plain', 
                    'style' => 'width:100%; height:200px;',
                    'placeholder' => '文章内容...'
                ])?>
                
                <?php ActiveForm::end(); ?>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Close'), [
                    'class'=>'btn btn-default',
                    'data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary',
                    'data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>
    
</div>

<?php

$renew = Url::to(['log-index', 'id' => $model->customer_id]);
$renewUrl = Url::to(['renew', 'id' => $model->customer_id]);

$js = 
<<<JS

    /** 富文本编辑器 */
    $('#container').removeClass('form-control');
    var ue = UE.getEditor('container', {
        toolbars:[
            [
                'fullscreen', 'source', '|', 
                'paragraph', 'fontfamily', 'fontsize', '|',
                'forecolor', 'backcolor', '|',
                'bold', 'italic', 'underline','fontborder', 'strikethrough', 'removeformat', 'formatmatch', '|', 
                'justifyleft', 'justifyright' , 'justifycenter', 'justifyjustify', '|',
                'insertorderedlist', 'insertunorderedlist', 'simpleupload', 'horizontal', '|',
                'selectall', 'cleardoc', 
                'undo', 'redo',  
            ]
        ]
    });
   
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$("#form-renew").submit();return;
        $.post("$renewUrl",$('#form-renew').serialize(),function(data){
            if(data['code'] == '200'){
                $("#actLog").load("$renew");
            }
        });
    });   
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>

<?php
    UeditorAsset::register($this);
?>
