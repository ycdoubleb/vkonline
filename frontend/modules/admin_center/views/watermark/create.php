<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\CustomerWatermark */

$this->title = Yii::t('app', '{Add}{Watermark}', [
    'Add' => Yii::t('app', 'Add'), 'Watermark'
]);

?>
<div class="customer-watermark-create main">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
