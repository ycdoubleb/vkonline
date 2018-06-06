<?php

use frontend\modules\callback\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '取消授权');

ModuleAssets::register($this);

?>
<div class="weibo-callback main">
    <div class="frame">
        <div class="page-title">取消授权</div>
        <div class="frame-content">
            <div class="content">
                <div class="bangding">
                    <h2 class="fs-title">取消授权成功！</h2>
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