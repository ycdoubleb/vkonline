<?php

use common\models\vk\Category;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model Category */

$this->title = Yii::t('app', "{Update}{Category}：{$model->name}", [
    'Update' => Yii::t('app', 'Update'), 'Category' => Yii::t('app', 'Category'),
]);

?>
<div class="category-update main">
        <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!-- 表单 -->
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>