<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\Category;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Category */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Course}{Category}',[
            'Course' => Yii::t('app', 'Course'),
            'Category' => Yii::t('app', 'Category'),
        ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-view customer">

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                'id',
                'name',
                'mobile_name',
                'level',
                'path',
                'parent_id',
                'sort_order',
                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'value' => !empty($model->image) ? Html::img(WEB_ROOT . $model->image, ['width' => '680px']) : null,
                ],
                'is_show',
                'des:ntext',
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>
