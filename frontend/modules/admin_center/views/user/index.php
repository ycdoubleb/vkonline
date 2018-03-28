<?php

use backend\components\GridViewChangeSelfColumn;
use common\models\searchs\UserSearch;
use common\models\User;
use frontend\modules\admin_center\assets\ModuleAssets;
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

?>
<div class="user-index main">
    <p>
        <?= Html::a(Yii::t('app', '{Create}{User}',[
            'Create' => Yii::t('app', 'Create'),
            'User' => Yii::t('app', 'User'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="frame">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => ['class' => 'table table-striped table-list'],
            'columns' => [
                [
                    'attribute' => 'username',
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'nickname',
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'sex',
                    'value' => function ($data){
                        return User::$sexName[$data['sex']];
                    },
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'sex',
                        'data' => User::$sexName,
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
                    'attribute' => 'status',
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'values' => [0,10],
                    ],
                    'value' => function ($data){
                        return User::$statusIs[$data['status']];
                    },
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'status',
                        'data' => User::$statusIs,
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
                    'attribute' => 'cour_num',
                    'label' => Yii::t('app', 'Course'),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px',
                        ],
                    ],
                    'value' => function($data) {
                        return (isset($data['cour_num']) ? $data['cour_num'] : 0 ). ' 门';
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'video_num',
                    'label' => Yii::t('app', 'Video'),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px',
                        ],
                    ],
                    'value' => function($data) {
                        return (isset($data['node_num']) ? $data['node_num'] : 0)  . ' 个';
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'max_store',
                    'filter' => false,
                    'format' => 'raw',
                    'value' => function($data) {
                        return Yii::$app->formatter->asShortSize($data['max_store'], 1) . ' / ' .
                                '<span style="color:' . (isset($data['user_size']) ? (($data['max_store'] - $data['user_size'] > $data['user_size']) ? 'green' : 'red') : 'green') . '">' . 
                                    Yii::$app->formatter->asShortSize((isset($data['user_size']) ? $data['user_size'] : '0'), 1) . '</span>';
                    },
                    'contentOptions' => [
                        'style' => [
                            'min-width' => '90px',
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_at',
                    'filter' => false,
                    'value' => function ($data){
                        return !empty($data['created_at']) ? date('Y-m-d H:i', $data['created_at']) : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}{delete}',
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
                             $options = [
                                'class' => 'btn btn-xs btn-default',
                                'style' => '',
                                'title' => Yii::t('app', 'Update'),
                                'aria-label' => Yii::t('app', 'Update'),
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-eye-open"></span>',
                                'url' => ['view', 'id' => $data['id']],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'update' => function ($url, $data, $key) {
                             $options = [
                                'class' => 'btn btn-xs btn-primary',
                                'style' => '',
                                'title' => Yii::t('app', 'Update'),
                                'aria-label' => Yii::t('app', 'Update'),
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-pencil"></span>',
                                'url' => ['update', 'id' => $data['id']],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'delete' => function ($url, $data, $key) {
                            $options = [
                                'class' => 'btn btn-xs btn-danger',
                                'style' => '',
                                'title' => Yii::t('app', 'Delete'),
                                'aria-label' => Yii::t('app', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-trash"></span>',
                                'url' => ['delete', 'id' => $data['id']],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                        },       
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
    ModuleAssets::register($this);
?>
