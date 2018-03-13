<?php

use backend\modules\system_admin\assets\SystemAssets;
use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CourseSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Course}{List}',[
    'Course' => Yii::t('app', 'Course'),
    'List' => Yii::t('app', 'List'),
]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="course-index customer">

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
                    'attribute' => 'category_id',
                    'label' => Yii::t('app', '{The}{Category}',[
                        'The' => Yii::t('app', 'The'),
                        'Category' => Yii::t('app', 'Category'),
                    ]),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'category_id',
                        'data' => $category,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
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
                    'label' => Yii::t('app', '{Course}{Name}',[
                        'Course' => Yii::t('app', 'Course'),
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
                    'attribute' => 'teacher_id',
                    'label' => Yii::t('app', '{Main Speak}{Teacher}',[
                        'Main Speak' => Yii::t('app', 'Main Speak'),
                        'Teacher' => Yii::t('app', 'Teacher'),
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'teacher_id',
                        'data' => $teacher,
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
                    'attribute' => 'created_by',
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'created_by',
                        'data' => $createdBy,
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
                    'attribute' => 'is_publish',
                    'label' => Yii::t('app', 'Status'),
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'is_publish',
                        'data' => Course::$satusPublish,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data){
                        return ($data->is_publish != null) ? Course::$satusPublish[$data->is_publish] : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [   //可见范围
                    'attribute' => 'level',
                    'label' => Yii::t('app', 'DataVisible Range'),
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'level',
                        'data' => Course::$levelStatus,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data){
                        return ($data['level'] != null) ? Course::$levelStatus[$data['level']] : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
//                    'attribute' => 'size',
                    'label' => Yii::t('app', '{Occupy}{Space}',[
                        'Occupy' => Yii::t('app', 'Occupy'),
                        'Space' => Yii::t('app', 'Space'),
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
//                    'attribute' => 'tags',
                    'label' => Yii::t('app', 'Tag'),
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'filter' => true,
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
                            'text-align' => 'center',
                        ],
                    ],
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
                    'template' => '{view}',
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