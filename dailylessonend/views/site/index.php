<?php

use common\components\aliyuncs\Aliyun;
use frontend\assets\SiteAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

$this->title = '每日一课';

SiteAssets::register($this);

?>

<div class="site-index">
    每日一课
</div>

<?php
$js = <<<JS
      
JS;
$this->registerJs($js, View::POS_READY);
?> 