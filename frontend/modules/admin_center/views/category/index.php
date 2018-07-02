<?php

use backend\components\GridViewChangeSelfColumn;
use backend\widgets\treegrid\TreegridAssets;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CategorySearch;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CategorySearch */
/* @var $modelProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Category}{Admin}',[
    'Category' => Yii::t('app', 'Category'),  'Admin' => Yii::t('app', 'Admin'),
]);

?>
<div class="category-index main">
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= $this->title ?>
            </span>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered detail-view vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'rowOptions' => function($model, $key, $index, $this){
                /* @var $model CategorySearch */
                return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
            },
            'columns' => [
                [
                    'attribute' => 'name',
                    'header' => Yii::t('app', 'Name'),
                    'headerOptions' => ['style' => 'width:300px'],
                    'contentOptions' => ['style' => 'text-align:left;'],
                ],
                [
                    'attribute' => 'mobile_name',
                    'header' => Yii::t('app', 'Mobile Name'),
                    'headerOptions' => ['style' => 'width:120px'],
                    'contentOptions' => [],
                ],
                [
                    'attribute' => 'courseAttribute.values',
                    'header' => Yii::t('app', 'Attribute'),
                    'value' => function ($model){
                        return count($model->courseAttribute) > 0 ? 
                            implode(',', ArrayHelper::getColumn($model->courseAttribute, 'values')) : null;
                    },
                    'contentOptions' => ['style' => 'min-width:100px; text-align: left;'],
                ],
//                [
//                    'attribute' => 'is_show',
//                    'header' => Yii::t('app', '{Is}{Show}',[
//                        'Is' => Yii::t('app', 'Is'),
//                        'Show' => Yii::t('app', 'Show'),
//                    ]),
//                    'class' => GridViewChangeSelfColumn::class,
//                    'filter' => Select2::widget([
//                        'model' => $searchModel,
//                        'attribute' => 'is_show',
//                        'data' => Category::$showStatus,
//                        'hideSearch' => true,
//                        'options' => ['placeholder' => Yii::t('app', 'All')],
//                        'pluginOptions' => [
//                            'allowClear' => true,
//                        ],
//                    ]),
//                    'value' => function ($model){
//                        return Category::$showStatus[$model->is_publish];
//                    },
//                    'disabled' => function($model) {
//                        return $model->parent_id == 0 ? true : (!empty(Course::findOne(['category_id' => $model->id])) 
//                                ? true : (!empty(Category::findOne(['parent_id' => $model->id]))
//                                    ? true : (count($model->courseAttribute) > 0 ? true : false)));
//                    },
//                    'headerOptions' => ['style' => 'width:80px'],
//                    'contentOptions' => ['style' => 'text-align:center;width:60px'],
//                ],
                [
                    'attribute' => 'sort_order',
                    'header' => Yii::t('app', 'Sort Order'),
                    'headerOptions' => ['style' =>'width:55px'],
                    'filter' => false,
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'type' => 'input',
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{add}{view}{update}{delete}',
                    'headerOptions' => ['style' => 'width:90px'],
                    'contentOptions' => [],
                    'buttons' => [
                        'add' => function ($url, $model, $key) {
                             $options = [
                                'class' => ($model->level > 3 ? 
                                    'disabled' : ''),
//                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'Create'),
                                'aria-label' => Yii::t('app', 'Create'),
                                'data-pjax' => '0',
                                'target' => '_blank'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-plus"></span>',
                                'url' => ['create', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'view' => function ($url, $model, $key) {
                             $options = [
                                'class' => '',
//                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'View'),
                                'aria-label' => Yii::t('app', 'View'),
                                'data-pjax' => '0',
                                'target' => '_blank'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-eye-open"></span>',
                                'url' => ['view', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'update' => function ($url, $model, $key) {
                             $options = [
                                'class' => $model->parent_id == 0 ? 'disabled' : '',
//                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'Update'),
                                'aria-label' => Yii::t('app', 'Update'),
                                'data-pjax' => '0',
                                'target' => '_blank'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-pencil"></span>',
                                'url' => ['update', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'delete' => function ($url, $model, $key){
                            $options = [
                                'class' => $model->parent_id == 0 ? 'disabled' : 
                                    (!empty(Course::findOne(['category_id' => $model->id])) 
                                        ? 'disabled' : (!empty(Category::findOne(['parent_id' => $model->id])) 
                                            ? 'disabled' : (count($model->courseAttribute) > 0 ? 'disabled' : ''))),
//                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'Delete'),
                                'aria-label' => Yii::t('app', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-trash"></span>',
                                'url' => ['delete', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>
<?php
    TreegridAssets::register($this);
    
    $js = <<<JS
        /**
         * 初始化树状网格插件
         */
        $('.table').treegrid({
            //initialState: 'collapsed',
        });
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>