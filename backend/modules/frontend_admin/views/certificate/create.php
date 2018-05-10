<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\TeacherCertificate */

$this->title = Yii::t('app', 'Create Teacher Certificate');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Teacher Certificates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="teacher-certificate-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
