<?php

use frontend\assets\AppAsset;
use common\models\User;
use dmstr\web\AdminLteAsset;
use frontend\modules\help_center\assets\HelpCenterAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */
/* @var $user User */

if (class_exists('frontend\assets\AppAsset')) {
    AppAsset::register($this);
} else {
    app\assets\AppAsset::register($this);
}
AdminLteAsset::register($this);

$user = Yii::$app->user->identity;
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <!--百度统计密钥-->
        <script type="text/javascript">
            var _hmt = _hmt || [];
            (function() {
                var hm = document.createElement("script");
                hm.src = "https://hm.baidu.com/hm.js?d0f0eac1cb855e1d9ce70e8b2e39b95a";
                var s = document.getElementsByTagName("script")[0]; 
                s.parentNode.insertBefore(hm, s);
            })();
        </script>
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
        <?php $this->beginBody() ?>
        <div class="wrapper">

            <?= $this->render('@frontend/views/layouts/navbar')?>
            
            <?= $this->render('left.php', $this->params);?>
            
            <div class="content-wrapper">
                <section class="content">
                    <?= $content ?>
                </section>
            </div>

        </div>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
<?php
    HelpCenterAssets::register($this);
?>