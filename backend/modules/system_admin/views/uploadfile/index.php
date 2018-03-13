<?php

use backend\modules\system_admin\assets\SystemAssets;
use common\modules\webuploader\models\searchs\UploadfileSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $searchModel UploadfileSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{File}{Administration}',[
    'File' => Yii::t('app', 'File'),
    'Administration' => Yii::t('app', 'Administration'),
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="uploadfile-index">
    <div class="frame frame-left">
        <div class="col-xs-12 frame-title">
            <span><?= Yii::t('null', '{Storage}{Info}',[
                'Storage' => Yii::t('app', 'Storage'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'label' => '总文件',
                    'value' => !empty($model['number']) ? $model['number'] . ' 个' : null,
                ],
                [
                    'label' => '总大小',
                    'value' => !empty($model['size']) ? Yii::$app->formatter->asShortSize($model['size']) : null,
                ],
            ]
        ]);?>
    </div>
    
    <div class="frame">
        <div class="col-xs-12 frame-title">
            <span><?= Yii::t('app', '{File}{List}',[
                'File' => Yii::t('app', 'File'),
                'List' => Yii::t('app', 'List'),
            ]) ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'id',
                    'label' => Yii::t('app', '{File}{ID}',[
                        'File' => Yii::t('app', 'File'),
                        'ID' => Yii::t('app', 'ID'),
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
                    'attribute' => 'name',
                    'label' => Yii::t('app', 'Name'),
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
                    'attribute' => 'path',
                    'label' => Yii::t('app', 'Path'),
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
                    'attribute' => 'created_by',
                    'label' => Yii::t('app', 'Upload By'),
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
                    'attribute' => 'created_at',
                    'label' => Yii::t('app', 'Created At'),
                    'filter' => false,
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'value' => function ($data){
                        return !empty(date('Y-m-d H:i', $data['created_at'])) ? date('Y-m-d H:i', $data['created_at']) : NULL;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'header' => Yii::t('app', 'Operating'),
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' => [
                    'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
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
