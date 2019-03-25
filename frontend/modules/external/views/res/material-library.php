<?php

use common\components\aliyuncs\Aliyun;
use frontend\assets\SiteAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
?>

<div class="res-material-library">
    <h1>素材库</h1>
</div>

<script>
    
</script>
<?php
$js = <<<JS
    
JS;
$this->registerJs($js, View::POS_READY);
?> 