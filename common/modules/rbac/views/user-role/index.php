<?php

use common\modules\rbac\models\searchs\AuthItemSearch;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel AuthItemSearch */
/* @var $dataProvider ArrayDataProvider */

$this->title = '用户角色';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-role-index rbac">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'username',
            'nickname',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'headerOptions' => [
                    'style' => 'width: 75px'
                ],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $options = [
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                        ];
                        return Html::a('分配', 
                            ['view', 'user_id' => $model->id], $options);
                    },
                ],
                'template' => '{view}',
            ],
        ],
    ]); ?>

</div>
