<?php

use frontend\modules\callback\assets\ModuleAssets;
use frontend\OAuths\qqAPI\core\QC;
use kartik\growl\GrowlAsset;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '授权');

ModuleAssets::register($this);
GrowlAsset::register($this);

?>

<div class="weibo-callback main">
    <div class="frame">
        <!--<div class="page-title">授权成功</div>-->
        <div class="frame-content">
        <?php
        
            $qc = new QC();

            $acs = $qc->qq_callback(); //access_token
            $oid = new $qc->get_openid();   //openid
            $qc = new QC($acs,$oid);
            $user_data = $qc->get_user_info();
            var_dump($user_data);exit;
        ?>
        </div>
    </div>
</div>
<?php

$js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
?>
