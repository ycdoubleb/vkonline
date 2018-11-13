<?php

use common\components\aliyuncs\Aliyun;
use common\models\User;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model User */
/* @var $form ActiveForm */

?>

<div class="user-form vk-form set-bottom">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],
        ],
    ]); ?>
        
    <!--真实名称-->
    <?= $form->field($model, 'nickname')->textInput([
        'maxlength' => true,
        'placeholder' => '真实名称',
        'disabled' => $model->isNewRecord ? false : true
   ])->label(Yii::t('app', 'Name')) ?>
    
    <!--用户名-->
    <?= $form->field($model, 'username')->textInput([
        'maxlength' => true, 
        'placeholder' => '用户名/手机号（注：用户名请不要用中文）',
        'disabled' => $model->isNewRecord ? false : true
    ]) ?>
    
    <!--密码-->
    <?= $form->field($model, 'password_hash')->passwordInput([
        'value' => '',  'placeholder' => '密码', 'minlength' => 6, 'maxlength' => 20
    ]) ?>
    
    <!--确认密码-->
    <?= $form->field($model, 'password2')->passwordInput([
        'placeholder' => '确认密码', 'minlength' => 6, 'maxlength' => 20
    ]) ?>
    
    <!--手机号码-->
    <?= $form->field($model, 'phone')->textInput(['placeholder' => '例如：12300000000', 'minlength' => 6, 'maxlength' => 20]); ?>

    <!--邮箱-->
    <?= $form->field($model, 'email')->textInput(['placeholder' => '例如：123@163.com', 'maxlength' => 200]) ?>
    
    <!--性别-->
    <?= $form->field($model, 'sex')->radioList(User::$sexName, [
        'itemOptions'=>[
            'labelOptions'=>[
                'style'=>[
                    'margin'=>'10px 15px 10px 0',
                    'color' => '#999',
                    'font-weight' => 'normal',
                ]
            ]
        ],
    ]); ?>
    
    <!--头像-->
    <?= $form->field($model, 'avatar')->widget(FileInput::classname(), [
        'options' => [
            'accept' => 'image/*',
            'multiple' => false,
        ],
        'pluginOptions' => [
            'resizeImages' => true,
            'showCaption' => false,
            'showRemove' => false,
            'showUpload' => false,
            'browseClass' => 'btn btn-primary btn-block',
            'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
            'browseLabel' => '选择上传头像...',
            'initialPreview' => [
                $model->isNewRecord ?
                        Html::img(Aliyun::absolutePath('upload/avatars/default.jpg'), ['class' => 'file-preview-image', 'width' => '130', 'height' => '130']) :
                        Html::img($model->avatar, ['class' => 'file-preview-image', 'width' => '130', 'height' => '130']),
            ],
            'overwriteInitial' => true,
        ],
    ]); ?>
    
    <!--存储空间-->
    <?php
//        $prompt = '1TB=1024GB，默认为不限制';
//        $downList = Html::dropDownList('User[byte]', null, User::$byteName, ['class' => 'form-control', 'style' => 'width: 88%;']);
//        echo $form->field($model, 'max_store',[
//            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\"><div class=\"col-lg-1 col-md-1 clear-padding\">{input}</div><div class=\"col-lg-1 col-md-1 clear-padding\">{$downList}</div>"
//                . "<div class=\"col-lg-3 col-md-3 clear-padding control-label\" style=\"color: #999;text-align: left;line-height:40px;\">{$prompt}</div></div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
//        ])->textInput(['type' => 'number', 'maxlength' => true, 'min' => 0]); 
    ?>
    
    <!--描述-->
    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>",
    ])->textarea(['rows' => 5, 'value' => $model->isNewRecord ? '无' : $model->des]) ?>

    <div class="form-group">
        <?= Html::label(null, null, ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-7 col-md-7">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success btn-flat']) ?>
        </div> 
    </div>

    <?php ActiveForm::end(); ?>

</div>
