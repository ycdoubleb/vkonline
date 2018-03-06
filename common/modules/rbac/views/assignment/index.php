<?php

use yii\helpers\Html;
use yii\grid\GridView;

use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\rbac\search\AuthItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Assignment';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="assignment-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php 
    Pjax::begin(['enablePushState'=>false]); 
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'class'=>'yii\grid\DataColumn',
                'attribute'=>$usernameField
            ],
            [
                'class'=>'yii\grid\DataColumn',
                'attribute'=>$nicknameField
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{view}'
            ],
        ],
    ]);
    Pjax::end();
    ?>
    
</div>
