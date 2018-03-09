<?php

use backend\modules\system_admin\assets\SystemAssets;
use common\models\searchs\UserSearch;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{User}{List}',[
    'User' => Yii::t('app', 'User'),
    'List' => Yii::t('app', 'List'),
]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index customer">
    <p>
        <?= Html::a(Yii::t('app', '{Create}{User}',[
            'Create' => Yii::t('app', 'Create'),
            'User' => Yii::t('app', 'User'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="col-xs-12 frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-list-ul"></i>
            <span><?= Yii::t('app', 'List') ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'customer_id',
                    'label' => Yii::t('app', '{The}{Customer}',[
                        'The' => Yii::t('app', 'The'),
                        'Customer' => Yii::t('app', 'Customer'),
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'customer_id',
                        'data' => $customer,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'username',
                    'label' => Yii::t('app', 'Account Number'),
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'nickname',
                    'label' => Yii::t('app', '{True}{Name}',[
                        'True' => Yii::t('app', 'True'),
                        'Name' => Yii::t('app', 'Name'),
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'sex',
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'value' => function ($data){
                        return !empty($data['sex']) ? common\models\User::$sexName[$data['sex']] : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                'status',
                'max_store',
                //'des:ntext',
                //'auth_key',
                //'created_at',
                //'updated_at',

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    SystemAssets::register($this);
?>
