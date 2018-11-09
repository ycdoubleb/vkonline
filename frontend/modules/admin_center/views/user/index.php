<?php

use common\models\searchs\UserSearch;
use common\models\User;
use common\models\vk\CustomerAdmin;
use common\widgets\grid\GridViewChangeSelfColumn;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserSearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{User}{List}',[
    'User' => Yii::t('app', 'User'), 'List' => Yii::t('app', 'List'),
]);

$userLevel = CustomerAdmin::find()->select(['level'])
    ->where(['user_id' => Yii::$app->user->id])->asArray()->one();   //当前用户的管理员等级

?>

<div class="user-index main">
    <div class="vk-panel clear-margin set-bottom">
        
        <div class="title">
            <span>
                <?= $this->title ?>
            </span>
            <div class="btngroup pull-right">
                <?= Html::a(Yii::t('app', '{Create}{User}',[
                    'Create' => Yii::t('app', 'Create'),
                    'User' => Yii::t('app', 'User'),
                ]), ['create'], ['class' => 'btn btn-success btn-flat', 'target' => '_blank']) ?>
            </div>
        </div>
        
        <div class="set-padding">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered vk-table'],
            'summaryOptions' => [
                'class' => 'summary',
                'style' => 'padding-left: 0px;'
            ],
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                [
                    'attribute' => 'username',
                    'headerOptions' => [
                        'style' => [
                            'width' => '135px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'nickname',
                    'headerOptions' => [
                        'style' => [
                            'width' => '105px',
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
                    'headerOptions' => [
                        'style' => [
                            'width' => '80px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'status',
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'labels' => ['停用','启用'],
                        'values' => [0,10],
                    ],
                    'disabled' => function($data) use ($userLevel){
                        return ($data['id'] == Yii::$app->user->id) ? true : 
                                (!empty($data['level']) ? ($userLevel['level'] >= $data['level'] ? true : false) : false);
                    },
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
                    'headerOptions' => [
                        'style' => [
                            'width' => '80px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'cour_num',
                    'label' => Yii::t('app', 'Course'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '105px',
                        ],
                    ],
                    'value' => function($data) {
                        return (isset($data['cour_num']) ? $data['cour_num'] : 0 ). ' 门';
                    },
                ],
                [
                    'attribute' => 'video_num',
                    'label' => Yii::t('app', 'Video'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '105px',
                        ],
                    ],
                    'value' => function($data) {
                        return (isset($data['node_num']) ? $data['node_num'] : 0)  . ' 个';
                    },
                ],
//                [
//                    'attribute' => 'max_store',
//                    'filter' => false,
//                    'format' => 'raw',
//                    'value' => function($data) {
//                        return Yii::$app->formatter->asShortSize($data['max_store'], 1) . ' / ' .
//                                '<span style="color:' . (isset($data['user_size']) ? (($data['max_store'] - $data['user_size'] > $data['user_size']) ? 'green' : 'red') : 'green') . '">' . 
//                                    Yii::$app->formatter->asShortSize((isset($data['user_size']) ? $data['user_size'] : '0'), 1) . '</span>';
//                    },
//                    'headerOptions' => [
//                        'style' => [
//                            'width' => '155px',
//                        ],
//                    ],
//                ],
                [
                    'attribute' => 'created_at',
                    'filter' => false,
                    'value' => function ($data){
                        return !empty($data['created_at']) ? date('Y-m-d H:i', $data['created_at']) : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '120px'
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'font-size' => '13px',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}{delete}',
                    'headerOptions' => ['style' => 'width:70px'],
                    'contentOptions' => ['style' => 'text-align:center;color:#666666'],
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
                             $options = [
                                'class' => '',
                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'View'),
                                'aria-label' => Yii::t('app', 'View'),
                                'data-pjax' => '0',
                                'target' => '_blank'
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
                        'update' => function ($url, $data, $key) use ($userLevel) {
                             $options = [
                                'class' => (($data['id'] == Yii::$app->user->id) ? ' ' : 
                                    (!empty($data['level']) ? ($userLevel['level'] >= $data['level'] ? 'disabled' : ' ') : ' ')),
                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'Update'),
                                'aria-label' => Yii::t('app', 'Update'),
                                'data-pjax' => '0',
                                'target' => '_blank'
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
                        'delete' => function ($url, $data, $key) use ($userLevel) {
                            $options = [
                                'class' => (($data['id'] == Yii::$app->user->id) ? 'disabled' : 
                                    (!empty($data['level']) ? ($userLevel['level'] >= $data['level'] ? 'disabled' : ' ') : ' ')),
                                'style' => 'color:#666666',
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
</div>
