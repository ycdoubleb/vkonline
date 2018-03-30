<?php

use frontend\modules\admin_center\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

?>

<div class="default-index main">
    <?= Html::img('/imgs/admin_center/images/404.jpg', ['width' => '100%']) ?>
</div>

<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>