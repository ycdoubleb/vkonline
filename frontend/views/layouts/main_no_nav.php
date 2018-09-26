<?php

use common\widgets\Alert;
use frontend\assets\AppAsset;
use frontend\assets\BaseAssets;
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

<div class="wrap" style="padding: 0 0 60px;">
    
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

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
