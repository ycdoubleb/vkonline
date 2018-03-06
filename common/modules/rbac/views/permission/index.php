<?php

use kartik\widgets\Select2;
use common\modules\rbac\models\searchs\AuthItemSearch;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel AuthItemSearch */
/* @var $dataProvider ArrayDataProvider */

$this->title = Yii::t('app/rbac', 'Permission');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="permission-index rbac">
    <p>
        <?= Html::a(Yii::t('app', 'Create').Yii::t('app/rbac', 'Permission'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php
    //Pjax::begin(['enablePushState'=>false]);
    
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'group_id',  
                'headerOptions' => ['style' => 'width: 240px;'],
                'format' => 'raw',
                'value' => function ($model) use($authGroups) {
                    /* @var $model AuthItemSearch */
                    return isset($authGroups[$model->group_id]) ? $authGroups[$model->group_id] : null;
                },
                'filter' => Select2::widget([
                    //'value' => null,
                    'model' => $searchModel,
                    'attribute' => 'group_id',
                    'data' => $authGroups,
                    'hideSearch'=>true,
                    'options' => ['placeholder' => Yii::t('app', 'Select Placeholder')],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])
            ],
            'name',
            'description:ntext',
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => [
                    'style' => 'width: 150px'
                ],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        /* @var $model AuthItemSearch */
                        $options = [
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                        ];
                        return Html::a(Yii::t('app/rbac', 'Edit'), 
                            ['view', 'id' => $model->name], $options);
                    },
                    'delete' => function ($url, $model, $key) {
                        /* @var $model AuthItemSearch */
                        $options = [
                            'class' => 'btn btn-danger',
                            'title' => Yii::t('yii', 'Delete'),
                            'aria-label' => Yii::t('yii', 'Delete'),
                            'data-pjax' => '0',
                            'data-method' => 'POST',
                        ];
                        return Html::a(Yii::t('app/rbac', 'Remove'), 
                            ['delete', 'id' => $model->name], $options);
                    },
                ],
                'template' => '{view} {delete}',
            ],
        ],
    ]); 
    //Pjax::end();
    ?>

</div>
