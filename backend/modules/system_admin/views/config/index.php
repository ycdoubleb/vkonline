<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\searchs\ConfigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t(null, '{Config}{Administration}',[
    'Config' => Yii::t('app', 'Config'),
    'Administration' => Yii::t('app', 'Administration'),
]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="config-index">

    <h1><?php //Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t(null, '{Create}{Config}',[
            'Create' => Yii::t('app', 'Create'),
            'Config' => Yii::t('app', 'Config'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute' => 'config_name',
                'label' => Yii::t(null, '{Config}{Name}',[
                    'Config' => Yii::t('app', 'Config'),
                    'Name' => Yii::t('app', 'Name'),
                ])
            ],
            [
                'attribute' => 'config_value',
                'label' => Yii::t(null, '{Config}{Value}',[
                    'Config' => Yii::t('app', 'Config'),
                    'Value' => Yii::t('app', 'Value'),
                ])
            ],
            //'config_name',
            //'config_value:ntext',
            'des:ntext',
            //'created_at',
            // 'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
