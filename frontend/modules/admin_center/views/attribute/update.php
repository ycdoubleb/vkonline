<?php

use common\models\vk\CourseAttribute;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model CourseAttribute */

$this->title = Yii::t('app', '{Update}{Attribute}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'Attribute' => Yii::t('app', 'Attribute'),
    'nameAttribute' => $model->name,
]);

?>
<div class="course-attribute-update main">
    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Update}{Attribute}',[
                    'Update' => Yii::t('app', 'Update'),
                    'Attribute' => Yii::t('app', 'Attribute'),
                ]) ?></span>
            </div>
            <div class="content-content">
                <?= $this->render('_form', [
                    'model' => $model,
                    'path' => $path,
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