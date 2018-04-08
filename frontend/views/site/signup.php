<?php

use common\models\User;
use frontend\assets\AppAsset;
use frontend\assets\SiteAssets;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model User */

$this->title = Yii::t('app', 'Signup');

?>
<div class="site-signup">
    <div class="vkonline" style='background-image: url("/imgs/site/site_loginbg.jpg");'>
        <div class="signup-title container">新用户注册</div>
        <div class="platform container">
            <div class="row">
                <div class="col-lg-12">
                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                        <?= Html::input('text', 'invite-code', '', [
                            'placeholder' => '邀请码',
                            'id' => 'invite-code',
                            'class' => 'form-control',
                        ])?>
                    
                        <?= $form->field($model, 'username')->textInput([
                            'maxlength' => true,
                            'placeholder' => '用户名',
                            'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
                        ]) ?>
                    
                        <?= $form->field($model, 'phone')->textInput([
                            'maxlength' => true,
                            'placeholder' => '手机号',
                        ]) ?>
    
                        <?= $form->field($model, 'password_hash')->passwordInput([
                            'minlength' => 6,
                            'maxlength' => 20,
                            'placeholder' => '密码...'
                        ]) ?>

                        <?= $form->field($model, 'password2')->passwordInput([
                            'minlength' => 6,
                            'maxlength' => 20,
                            'placeholder' => '请确认登录密码...'
                        ]) ?>
                        
                        <?= $form->field($model, 'nickname')->textInput([
                            'maxlength' => true,
                            'placeholder' => '真实名称'
                        ]) ?>
                        
                        <div class="form-group btn-signup">
                            <?= Html::submitButton(Yii::t('app', 'Signup'), [
                                'class' => 'btn btn-primary signup-button', 'name' => 'signup-button'
                            ]) ?>
                        </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
   
    /** 滚动到登录框 */
    $('html,body').animate({scrollTop: ($(".platform").offset().top) - 120}, 200);
JS;
    $this->registerJs($js, View::POS_READY);
?>

<?php
    AppAsset::register($this);
    SiteAssets::register($this);
?>