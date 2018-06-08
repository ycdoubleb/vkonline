<?php

use common\models\User;
use frontend\modules\callback\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model User */

$this->title = Yii::t('app', '授权');

ModuleAssets::register($this);
GrowlAsset::register($this);

?>
<div class="weibo-callback main">
    <div class="frame">
        <div class="page-title">授权成功</div>
        <div class="frame-content">
            <div class="content">
                <div class="bangding">
                    <h2 class="fs-title">授权成功！绑定已有账号</h2>
                    <?php $form = ActiveForm::begin([
                        'options' => [
                            'id' => 'msform',
                            'class' => 'form-horizontal',
                            'enctype' => 'multipart/form-data',
                            'onkeydown' => 'if(event.keyCode==13){return false;}', //去掉form表单的input回车提交事件
                        ],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
                            'labelOptions' => ['class' => 'control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
                        ],
                    ]); ?>
                        <?= $form->field($model, 'username')->textInput(['maxlength' => true,'placeholder' => '用户名...'])
                            ->label('')?>
                        <?= $form->field($model, 'password_hash')->passwordInput(['minlength' => 6,'maxlength' => 20,
                        'placeholder' => '密码...'])->label('') ?>
                    <?php ActiveForm::end();?>
                    <input type="submit" name="submit" id="binding" class="btn btn-primary btn-flat" value="绑定" />
                </div>
                <h3 class="fs-subtitle">无已有账号直接点击“完成注册”</h3>
                <input type="submit" name="submit" id="signup" class="btn btn-success btn-flat" value="完成注册" />
            </div>
        </div>
    </div>
</div>
<?php

$js = <<<JS
        
    //绑定用户
    $("#binding").click(function(){
        $.post("/callback/weibo-callback/binding-user",$('#msform').serialize(),function(data){
            if(data['code'] == '200'){
                $.notify({
                    message: data['message'],
                },{
                    type: "success",
                });
                $("#binding").addClass("disabled");
            }else if(data['code'] == '417'){
                $.notify({
                    message: data['message'],
                },{
                    type: "danger",
                });
            }
        });
    });
      
    //直接提交注册
    $("#signup").click(function(){
        if($("binding").hasClass("disabled")){
            $.post("/callback/weibo-callback/signup?type=1");
        }else{
            $.post("/callback/weibo-callback/signup?type=2");
        }
    })
        
JS;
    $this->registerJs($js, View::POS_READY);
?>