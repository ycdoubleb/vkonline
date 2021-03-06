<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\LinkPager;

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
            <div style="float:right">
                <?= Html::a(Yii::t('app', 'Course Import'), ['course-import'],['class' => 'btn btn-default']) ?>
                <?= Html::a(Yii::t('app', 'Course Node Import'), ['course-node-import'],['class' => 'btn btn-default']) ?>
            </div>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'customer_name',
                    'label' => Yii::t('app', '{The}{Customer}',[
                        'The' => Yii::t('app', 'The'),
                        'Customer' => Yii::t('app', 'Customer'),
                    ]),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'customer_id',
                        'data' => $customer,
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
                    'attribute' => 'category_name',
                    'label' => Yii::t('app', '{The}{Category}',[
                        'The' => Yii::t('app', 'The'),
                        'Category' => Yii::t('app', 'Category'),
                    ]),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'category_id',
                        'data' => $category,
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
                    'attribute' => 'name',
                    'label' => Yii::t('app', '{Course}{Name}',[
                        'Course' => Yii::t('app', 'Course'),
                        'Name' => Yii::t('app', 'Name'),
                    ]),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'teacher_name',
                    'label' => Yii::t('app', '{Main Speak}{Teacher}',[
                        'Main Speak' => Yii::t('app', 'Main Speak'),
                        'Teacher' => Yii::t('app', 'Teacher'),
                    ]),
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
                    'attribute' => 'nickname',
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
                        ],
                    ],
                ],
                [   //可见范围
                    'attribute' => 'level',
                    'label' => Yii::t('app', 'Range'),
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
                        return ($data['level'] != null) ? '<span style="color:' . ($data['is_publish'] == 0 ? '#999999' : ' ') . '">' . 
                                    Course::$levelMap[$data['level']] . '</span>' : null;
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
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
                            $options = [
                                'class' => 'btn btn-sm '.($data['is_publish'] == 0 ? 'disabled' : ' '),
                                'style' => 'padding:0px; display:unset',
                                'title' => Yii::t('app', 'View'),
                                'aria-label' => Yii::t('app', 'View'),
                                'data-pjax' => '0',
                                'target' => '_blank',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-eye-open"></span>',
                                'url' => Url::to([WEB_ROOT . '/course/default/view', 'id' => $data['id']], 'http'),
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
    
    <?php
        $page = !isset($filters['page']) ? 1 : $filters['page'];
        $pageCount = ceil($totalCount / 20);
        if($pageCount > 0){
            echo '<div class="summary">' . 
                    '第<b>' . (($page * 20 - 20) + 1) . '</b>-<b>' . ($page != $pageCount ? $page * 20 : $totalCount) .'</b>条，总共<b>' . $totalCount . '</b>条数据。' .
                '</div>';
        }

        echo LinkPager::widget([  
            'pagination' => new Pagination([
                'totalCount' => $totalCount,  
            ]),  
        ])
    ?>
    
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>