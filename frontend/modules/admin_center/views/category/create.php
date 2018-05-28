<?php

use common\models\vk\Category;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Category */

?>
<div class="category-create main">
    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Create}{Category}',[
                    'Create' => Yii::t('app', 'Create'),
                    'Category' => Yii::t('app', 'Category'),
                ]) ?></span>
            </div>
            <div class="content-content">
                <?= $this->render('_form', [
                    'model' => $model,
                    'parentModel' => $parentModel,
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
