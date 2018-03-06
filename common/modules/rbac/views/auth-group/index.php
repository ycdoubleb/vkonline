<?php

use common\modules\rbac\models\searchs\AuthGroupSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel AuthGroupSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app/rbac', 'Auth Groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-group-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create').Yii::t('app/rbac', 'Auth Group'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            'app',
            'sort_order',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
