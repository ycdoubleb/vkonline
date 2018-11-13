<?php

use common\widgets\Alert;
use dailylessonend\assets\AppAsset;
use dailylessonend\assets\BaseAssets;
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
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
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
        <?= $this->render('model') ?>
    </div>
</div>

<footer class="footer">
    <div class="copy-right-box">
        <div class="container copy-right-content">
            <p class="pull-left">eenet旗下品牌，Copyright © <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?> 版权所有 <a href="http://www.miitbeian.gov.cn">粤ICP备14084579号-7</a></p>
            <p class="pull-right"><img src="/imgs/site/logo_hui.png"/></p>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
