<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\vk\searchs\CourseAttributeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '{Course}{Attribute}',[
    'Course' => Yii::t('app', 'Course'),
    'Attribute' => Yii::t('app', 'Attribute'),
]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="course-attribute-index">

    <p>
        <?= Html::a(Yii::t('app', '{Create}{Course}{Attribute}', [
            'Create' => Yii::t('app', 'Create'),
            'Course' => Yii::t('app', 'Course'),
            'Attribute' => Yii::t('app', 'Attribute'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'value' => function ($model){
                    return $model->name;
                },
            ],
            [
                'attribute' => 'type',
                'value' => function ($model){
                    return $model->type;
                },
            ],
            [
                'attribute' => 'input_type',
                'label' => Yii::t('app', '{Input}{Type}',[
                    'Input' => Yii::t('app', 'Input'),
                    'Type' => Yii::t('app', 'Type'),
                ]),
                'value' => function ($model){
                    return $model->input_type;
                },
            ],
            [
                'attribute' => 'values',
                'value' => function ($model){
                    return $model->values;
                },
            ],
            [
                'attribute' => 'sort_order',
                'value' => function ($model){
                    return $model->sort_order;
                },
            ],
            //'index_type',
            //'is_del',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
