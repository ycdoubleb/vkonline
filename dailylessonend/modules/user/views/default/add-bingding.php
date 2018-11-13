<?php

use common\models\vk\CustomerAdmin;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;


/* @var $this View */
/* @var $model CustomerAdmin */

$this->title = '绑定品牌';

?>

<div class="customer-add-bingding main vk-modal">

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
                        'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">"
                            . "{input}</div>\n<div class=\"col-lg-12 col-md-12\">{error}</div>",  
                        'labelOptions' => [
                            'class' => 'col-lg-12 col-md-12',
                        ],  
                    ], 
                ]); ?>
                    
                    <!--用户id-->    
                    <?= Html::activeHiddenInput($model, 'user_id') ?>
                    
                    <?= $form->field($model, 'brand_id')->textInput(['value' => '',
                    'placeholder' => '邀请码...'])->label('')?>
                    <!--客户名或注释信息-->
                    <div id="customer" class="name-info"><span></span></div>
                    
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
        
    //输入邀请码后触发
    $('#userbrand-brand_id').blur(function() {
        var txtVal=$("#userbrand-brand_id").val();     //获取输入的内容
        if(txtVal != ""){
            $.post("/user/default/customer", {'txtVal': txtVal}, function (rel) {
                if (rel['code'] == 200) {
                    $("#userbrand-brand_id").after('<i class="icon fa fa-check-circle icon-y"></i>');
                    $("#customer > span").html(rel['data']['name']);
                }else{
                    $("#userbrand-brand_id").after('<i class="icon fa fa-times-circle icon-n"></i>');
                    $("#customer > span").html('<span style="color:#a94442">无效的邀请码</span>');
                }
            })
        }
    });

    //当邀请码输入框内容被更改时
    $("#userbrand-brand_id").bind("input propertychange change",function(event){
        $(".icon").remove();              //移除右侧图标
        $("#customer > span").empty();    //移除客户名或注释
    });
    
    //提交表单
    $("#submitsave").click(function(){
        $.post("/user/default/add-bingding?user_id={$model->user_id}", $('#form-admin').serialize(),function(data){
            location.reload();
        });
    });   
JS;
    $this->registerJs($js,  View::POS_READY);
?>
