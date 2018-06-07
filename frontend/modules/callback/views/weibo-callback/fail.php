<?php

use frontend\modules\callback\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '授权失败');

ModuleAssets::register($this);

?>
<div class="weibo-callback main">
    <div class="frame">
        <div class="page-title">授权失败</div>
        <div class="frame-content">
            <div class="content">
                <div class="bangding">
                    <h2 class="fs-title">授权失败！请重新<a href="<?= $weibo_url?>">登录授权</a>！</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<?php

$js = <<<JS
      
JS;
    $this->registerJs($js, View::POS_READY);
?>