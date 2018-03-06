<?php

use common\models\AdminUser;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model AdminUser */
/* @var $form ActiveForm */
?>

<div class="user-form">
    <?php
    $form = ActiveForm::begin([
                'options' => [
                    'class' => 'form-horizontal',
                    'enctype' => 'multipart/form-data',
                ],
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-9 col-md-9\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-2 col-md-2 control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
                ],
    ]);
    ?>
    <div class="col-lg-7 col-md-7">

        <?php
        echo ($model->isNewRecord ? "" : $form->field($model, 'id')->textInput(['maxlength' => 32, 'readonly' => 'true']));
        ?>

        <?php echo $form->field($model, 'username')->textInput(['maxlength' => 32]); ?>

        <?php echo $form->field($model, 'nickname')->textInput(['maxlength' => 32]); ?>

        <?php echo $form->field($model, 'password_hash')->passwordInput(['minlength' => 6, 'maxlength' => 20]); ?>

        <?php echo $form->field($model, 'password2')->passwordInput(['minlength' => 6, 'maxlength' => 20]); ?>

        <?php echo $form->field($model, 'sex')->radioList(AdminUser::$sexName); ?>

        <?php echo $form->field($model, 'guid')->textInput(['minlength' => 6, 'maxlength' => 20]); ?>

        <?php echo $form->field($model, 'phone')->textInput(['minlength' => 6, 'maxlength' => 20]); ?>

<?php echo $form->field($model, 'email')->textInput(['maxlength' => 200]) ?>
    </div>

    <div class="col-lg-5 col-md-5" >
        <?php
        echo $form->field($model, 'avatar')->widget(FileInput::classname(), [
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
                'browseLabel' => '选择上传图像...',
                'initialPreview' => [
                    $model->isNewRecord ?
                            Html::img(WEB_ROOT . '/resources/avatars/timg.jpg', ['class' => 'file-preview-image', 'width' => '213']) :
                            Html::img(WEB_ROOT . $model->avatar, ['class' => 'file-preview-image', 'width' => '213']),
                ],
                'overwriteInitial' => true,
            ],
        ]);
        ?>
    </div>

    <div class="col-lg-10 col-md-10 form-group">
    <?= Html::submitButton($model->isNewRecord ? '增加用户' : '编辑用户', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'])
    ?> 
    </div>
<?php $form->end(); ?>
</div>

