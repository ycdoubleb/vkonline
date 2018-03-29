<?php

use common\models\Banner;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model Banner */

$this->title = Yii::t('app', '{Update}{Banner}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'Banner' => Yii::t('app', 'Banner'),
    'nameAttribute' => $model->title,
]);

?>
<div class="banner-update main">
    <div class="frame">
        <div class="frame-title">
            <i class="icon fa fa-edit"></i>
            <span><?= Yii::t('app', '{Create}{User}',[
                'Create' => Yii::t('app', 'Create'),
                'User' => Yii::t('app', 'User'),
            ]) ?></span>
        </div>
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>
