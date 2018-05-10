<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\CourseAttribute;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model CourseAttribute */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Course}{Attribute}',[
    'Course' => Yii::t('app', 'Course'),
    'Attribute' => Yii::t('app', 'Attribute'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="course-attribute-view customer">

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
                [
                    'attribute' => 'category_id',
                    'value' => function ($model) use($categoryName){
                        return $categoryName[$model->category_id];
                    },
                ],
                [
                    'attribute' => 'type',
                    'value' => function ($model){
                        return CourseAttribute::$type_keys[$model->type];
                    },
                ],
                [
                    'attribute' => 'input_type',
                    'value' => function ($model){
                        return CourseAttribute::$input_type_keys[$model->input_type];
                    },
                ],
                [
                    'attribute' => 'index_type',
                    'value' => function ($model){
                        return CourseAttribute::$index_type_keys[$model->index_type];
                    },
                ],
                'sort_order',
                'values:ntext',
                [
                    'attribute' => 'is_del',
                    'value' => function ($model){
                        return $model->is_del == 0 ? '否' : '是';
                    },
                ],
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