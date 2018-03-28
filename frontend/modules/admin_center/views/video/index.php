<?php

use common\models\vk\Course;
use common\models\vk\searchs\VideoSearch;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel VideoSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Video}{List}',[
    'Video' => Yii::t('app', 'Video'),
    'List' => Yii::t('app', 'List'),
]);

?>
<div class="video-index main">

    <div class="frame">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => ['class' => 'table table-striped table-list'],
            'columns' => [
                [
                    'attribute' => 'courseNode.course.name',
                    'label' => Yii::t('app', '{The}{Course}',[
                        'The' => Yii::t('app', 'The'),
                        'Course' => Yii::t('app', 'Course'),
                    ]),
                    'filter' => Html::input('text', 'VideoSearch[course_name]', 
                            ArrayHelper::getValue($filters, 'VideoSearch.course_name'), ['class' => 'form-control']),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                        ],
                    ],
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', '{Video}{Name}',[
                        'Video' => Yii::t('app', 'Video'),
                        'Name' => Yii::t('app', 'Name'),
                    ]),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                        ],
                    ],
                ],
                [
                    'attribute' => 'teacher.name',
                    'label' => Yii::t('app', 'Teacher'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'teacher_id',
                        'data' => $teacher,
                        'hideSearch' => false,
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
                    'attribute' => 'createdBy.nickname',
                    'label' => Yii::t('app', 'Created By'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'created_by',
                        'data' => $createdBy,
                        'hideSearch' => false,
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
                    'attribute' => 'is_publish',
                    'label' => Yii::t('app', 'Status'),
                    'format' => 'raw',
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'is_publish',
                        'data' => Course::$publishStatus,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data){
                        return ($data['is_publish'] != null) ? '<span style="color:' . ($data['is_publish'] == 0 ? '#999999' : ' ') . '">' . 
                                    Course::$publishStatus[$data['is_publish']] . '</span>' : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'width' => '65px'
                        ],
                    ],
                ],
                [   //可见范围
                    'attribute' => 'level',
                    'label' => Yii::t('app', 'DataVisible Range'),
                    'format' => 'raw',
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'level',
                        'data' => Course::$levelMap,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data){
                        return ($data['level'] != null) ?  '<span style="color:' . ($data['is_publish'] == 0 ? '#999999' : ' ') . '">' . 
                                Course::$levelMap[$data['level']] . '</span>' : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'is_ref',
                    'label' => Yii::t('app', 'Quote'),
                    'format' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return $data['is_ref'] == 1 ? '<span style="color:red">引用</span>' : '<span style="color:green">原创</span>';
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'source.size',
                    'label' => Yii::t('app', '{Video}{Size}',[
                        'Video' => Yii::t('app', 'Video'),
                        'Size' => Yii::t('app', 'Size'),
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '80px',
                        ],
                    ],
                    'value' => function ($data){
                        return Yii::$app->formatter->asShortSize($data['source']['size'], 1);
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Attachment}{Size}',[
                        'Attachment' => Yii::t('app', 'Attachment'),
                        'Size' => Yii::t('app', 'Size'),
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '80px',
                        ],
                    ],
                    'value' => function ($data){
                        return Yii::$app->formatter->asShortSize((isset($data['att_size']) ? $data['att_size'] : 0), 1);
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
//                    'attribute' => 'tags',
                    'label' => Yii::t('app', 'Tag'),
                    'filter' => true,
//                    'value' => function ($data){
//                        return ($data['tags'] != null) ? $data['tags'] : null;
//                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_at',
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px'
                        ],
                    ],
                    'filter' => false,
                    'value' => function ($data){
                        return !empty($data['created_at']) ? date('Y-m-d H:i', $data['created_at']) : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
                             $options = [
                                'class' => 'btn btn-xs btn-default '.($data['is_publish'] == 0 ? 'disabled' : ' '),
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
