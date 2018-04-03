<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\Good;
use common\models\vk\searchs\GoodSearch;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel GoodSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Good ID}{Admin}',[
    'Good ID' => Yii::t('app', 'Good ID'),
    'Admin' => Yii::t('app', 'Admin'),
]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="good-index customer">
    
    <p>
        <?= Html::a(Yii::t('app', '{Create}{Good ID}',[
            'Create' => Yii::t('app', 'Create'),
            'Good ID' => Yii::t('app', 'Good ID'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="frame">
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
                    'attribute' => 'name',
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'type',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return !empty($data->type) ? Good::$sizeType[$data->type] : null;
                    },
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'type',
                        'data' => Good::$sizeType,
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
                    'attribute' => 'data',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return !empty($data->data) ? Yii::$app->formatter->asShortSize($data->data) : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return !empty($data->price) ? $data->price . '元/月' : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                'des',
                //'created_at',
                //'updated_at',

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
    <div style="height: 130px"></div>
</div>
<?php

    $js = <<<JS

JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>
