<?php

use common\models\vk\Good;
use yii\web\View;


/* @var $this View */
/* @var $model Good */

$this->title = Yii::t('app', '{Create}{Good ID}',[
    'Create' => Yii::t('app', 'Create'),
    'Good ID' => Yii::t('app', 'Good ID'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Create}{Good ID}',[
            'Create' => Yii::t('app', 'Create'),
            'Good ID' => Yii::t('app', 'Good ID'),
        ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="good-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
