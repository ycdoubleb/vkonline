<?php

use common\models\vk\Category;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model Category */

$this->title = Yii::t('app', '{Update}{Category}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'Category' => Yii::t('app', 'Category'),
    'nameAttribute' => $model->name,
]);

?>
<div class="category-update main">
    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Update}{Category}',[
                    'Update' => Yii::t('app', 'Update'),
                    'Category' => Yii::t('app', 'Category'),
                ]) ?></span>
            </div>
            <div class="content-content">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>