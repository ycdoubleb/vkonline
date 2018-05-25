<?php

use common\widgets\Alert;
use frontend\assets\AppAsset;
use kartik\widgets\AlertBlock;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Breadcrumbs;

/* @var $this View */
/* @var $content string */


AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta baidu-gxt-verify-token="ba7c0074cb8ad4749087e8b482ab0afa"><!--百度云观测密钥-->
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
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    
    <?= $this->render('navbar') ?>

    <div>
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= AlertBlock::widget([
            'useSessionFlash' => TRUE,
            'type' => AlertBlock::TYPE_GROWL,
            'delay' => 0
        ]);?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="contacts-box">
        <div class="container contacts-content">
            <ul>
                <li class="contacts-left"><?= Html::a('关于我们', '/other/default/about') ?></li>
                <li class="contacts-left"><?= Html::a('联系我们', '/other/default/contact') ?></li>
                <li class="contacts-left"><?= Html::a('意见反馈', '/other/default/feedback') ?></li>
                <li class="contacts-left"><?= Html::a('帮助中心', '/help_center/default/?app_id=app-frontend&id=1') ?></li>
            </ul>
            <ul>
            </ul>
            <ul class="contacts-right-box">
                <li class="contacts-right">
                    <span class="icon icon-phone"></span>
                    <p class="title">000-00000000</p>
                    <p class="time">周一至周日 9：00—21：00</p>
                </li>
                <li class="contacts-right">
                    <span class="icon icon-qq"></span>
                    <p class="title"><?= Html::a('在线QQ客服', 'feedback') ?></p>
                    <p class="time">周一至周日 9：00—21：00</p>
                </li>
            </ul>
        </div>
    </div>
    <div class="copy-right-box">
        <div class="container copy-right-content">
            <p class="pull-left">eenet旗下品牌，Copyright © <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?> 版权所有  京ICP备150033??号-1   京公网安备 110105020347??号</p>
            <p class="pull-right"><img src="/imgs/site/logo_hui.png"/></p>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
