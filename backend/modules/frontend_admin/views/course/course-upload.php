<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="course-import-index">

    <?php ActiveForm::begin([
         'options' => [
            'enctype' => 'multipart/form-data',
        ],
    ]); ?>
    <div class="form-group">
        <label class='control-label'>选择要导入的表格：</label>
        <?= Html::input('file', 'import-file') ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>