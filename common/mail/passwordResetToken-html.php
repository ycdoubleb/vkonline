<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<div class="password-reset" style="font-size: 16px;">
    <p style="font-weight: bold">尊敬的<?= Html::encode($user->username) ?>：</p>

    <p style="text-indent: 2em">您好，这是一封（<a href="<?= WEB_ROOT;?>">游学吧</a>）重置密码的请求邮件。</p>
    <p><span style="font-weight: bold">请点击以下链接进行重置密码：</span><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
    <p>如果不能点击，请复制地址到浏览器，然后直接打开。</p>
    <p style="font-weight: bold;text-align: center">游学吧</p>
</div>
