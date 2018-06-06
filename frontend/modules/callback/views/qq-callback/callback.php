<?php

use frontend\modules\callback\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

$this->title = Yii::t('app', '授权');

ModuleAssets::register($this);
GrowlAsset::register($this);

?>

<div class="weibo-callback main">
    <div class="frame">
        <div class="page-title">授权成功</div>
        <div class="frame-content">
        <?php
            require_once("/../OAuths/qqAPI/qqConnectAPI.php");
            $qc = new QC();
            echo $qc->qq_callback();
            echo $qc->get_openid();
        ?>
        </div>
    </div>
</div>
<?php

$js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
?>
