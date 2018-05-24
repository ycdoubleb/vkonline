<?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use common\widgets\depdropdown\DepDropdown;
use frontend\modules\admin_center\assets\ModuleAssets;
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

?>
<div class="course-index main">

    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Course}{List}',[
                    'Course' => Yii::t('app', 'Course'),
                    'List' => Yii::t('app', 'List'),
                ]) ?></span>
            </div>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'layout' => "{items}\n{summary}\n{pager}",
                'summaryOptions' => ['class' => 'hidden'],
                'pager' => [
                    'options' => ['class' => 'hidden']
                ],
                'columns' => [
                    [
                        'attribute' => 'category_id',
                        'label' => Yii::t('app', 'Category'),
                        'value' => function($data) {
                            /** @var $model Course **/
                            $model= Course::findOne(['id' => $data['id']]);
                            return $model->category->fullPath;
                        },
                        'filter' => DepDropdown::widget([
                            'model' => $searchModel,
                            'attribute' => 'category_id',
                            'plugOptions' => [
                                'url' => Url::to('search-children', false),
                                'level' => 3,
                            ],
                            'items' => Category::getSameLevelCats($searchModel->category_id, true),
                            'values' => $searchModel->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($searchModel->category_id)->path))),
                            'itemOptions' => [
                                'style' => 'display:inline-block;',
                            ],
                        ]),
                        'headerOptions' => ['style' => 'width:248px'],
                        'contentOptions' => ['style' => 'white-space:normal'],
                    ],
                    [
                        'attribute' => 'name',
                        'label' => Yii::t('app', '{Course}{Name}',[
                            'Course' => Yii::t('app', 'Course'),
                            'Name' => Yii::t('app', 'Name'),
                        ]),
                        'headerOptions' => ['style' => 'width:140px'],
                        'contentOptions' => ['style' => 'white-space:normal'],
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
                        'headerOptions' => ['style' => 'width:65px'],
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
                        'headerOptions' => ['style' => 'width:60px'],
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
                        'headerOptions' => ['style' => 'width:60px'],
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
                        'headerOptions' => ['style' => 'width:60px'],
                    ],
                    [
                        'label' => Yii::t('app', 'Tag'),
                        'filter' => true,
                        'value' => function ($data){
                            return (isset($data['tags'])) ? $data['tags'] : null;
                        },
                        'headerOptions' => ['style' => 'width:100px'],
                        'contentOptions' => ['style' => 'white-space:normal'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'headerOptions' => ['style' => 'width:30px'],
                        'buttons' => [
                            'view' => function ($url, $data, $key) {
                                 $options = [
                                    'class' => ($data['is_publish'] == 0 ? 'disabled' : ' '),
                                    'style' => '',
                                    'title' => Yii::t('app', 'View'),
                                    'aria-label' => Yii::t('app', 'View'),
                                    'data-pjax' => '0',
                                ];
                                $buttonHtml = [
                                    'name' => '<span class="glyphicon glyphicon-eye-open"></span>',
                                    'url' => ['/course/default/view', 'id' => $data['id']],
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
            
            <div class="course-bottom">
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
                    ])?>
            </div>  
        </div>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>