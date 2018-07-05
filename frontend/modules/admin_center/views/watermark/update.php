<?php

use common\models\vk\CustomerWatermark;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model CustomerWatermark */

ModuleAssets::register($this);

$this->title = Yii::t('app', "{Update}{Watermark}：{$model->name}", [
    'Update' => Yii::t('app', 'Update'), 'Watermark' => Yii::t('app', 'Watermark')
]);

?>
<div class="customer-watermark-update main">
    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!--表单-->
    <?= $this->render('_form', [
        'model' => $model,
        'files' => $files
    ]) ?>

</div>
