<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\webuploader\models\Uploadfile */

$this->title = Yii::t('app', 'Create Uploadfile');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Uploadfiles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="uploadfile-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
