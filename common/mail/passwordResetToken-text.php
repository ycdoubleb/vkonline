<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
尊敬的<?= $user->username ?>：

    您好，这是一封（游学吧）重置密码的请求邮件。
请点击以下链接进行重置密码：<?= $resetLink ?>

如果不能点击，请复制地址到浏览器，然后直接打开。

            游学吧
