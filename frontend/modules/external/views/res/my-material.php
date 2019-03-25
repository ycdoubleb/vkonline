<?php

use common\components\aliyuncs\Aliyun;
use frontend\assets\SiteAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
?>

<div class="res-my-material">
    <h1>我的素材</h1>
</div>

<script>
    
</script>
<?php
$js = <<<JS
    
JS;
$this->registerJs($js, View::POS_READY);
?> 