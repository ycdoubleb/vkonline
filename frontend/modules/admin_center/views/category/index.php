<?php

use backend\components\GridViewChangeSelfColumn;
use backend\widgets\treegrid\TreegridAssets;
use common\models\vk\Category;
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

?>
<div class="category-index main">

    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Category}{Admin}',[
                    'Category' => Yii::t('app', 'Category'),
                    'Admin' => Yii::t('app', 'Admin'),
                ]) ?></span>
                <div class="framebtn">
                    <?= Html::a(Yii::t('app', 'Add'), ['create'], ['class' => 'btn btn-success',
                        'style' => 'line-height:22px;', 'target' => '_blank']) ?>
                </div>
            </div>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'layout' => "{items}\n{summary}\n{pager}",
                'rowOptions' => function($model, $key, $index, $this){
                    /* @var $model CategorySearch */
                    return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
                },
                'columns' => [
                    [
                        'attribute' => 'name',
                        'header' => Yii::t('app', 'Name'),
                        'headerOptions' => ['style' => 'min-width:200px'],
                        'contentOptions' => ['style' => 'text-align:left'],
                    ],
                    [
                        'attribute' => 'mobile_name',
                        'header' => Yii::t('app', 'Mobile Name'),
                        'headerOptions' => ['style' => 'width:120px'],
                        'contentOptions' => ['style' => 'text-align:left'],
                    ],
                    [
                        'attribute' => 'courseAttribute.values',
                        'header' => Yii::t('app', 'Attribute'),
                        'value' => function ($model){
                            return count($model->courseAttribute) > 0 ? 
                                implode(',', ArrayHelper::getColumn($model->courseAttribute, 'values')) : null;
                        },
                        'contentOptions' => ['style' => 'min-width:200px;text-align:left;white-space:unset'],
                    ],
                    [
                        'attribute' => 'is_show',
                        'header' => Yii::t('app', '{Is}{Show}',[
                            'Is' => Yii::t('app', 'Is'),
                            'Show' => Yii::t('app', 'Show'),
                        ]),
                        'class' => GridViewChangeSelfColumn::class,
                        'filter' => Select2::widget([
                            'model' => $searchModel,
                            'attribute' => 'is_show',
                            'data' => Category::$showStatus,
                            'hideSearch' => true,
                            'options' => ['placeholder' => Yii::t('app', 'All')],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]),
                        'value' => function ($model){
                            return Category::$showStatus[$model->is_publish];
                        },
                        'disabled' => function($model) {
                            return count($model->courseAttribute) > 0 ? true : false;
                        },
                        'headerOptions' => ['style' => 'width:80px'],
                        'contentOptions' => ['style' => 'text-align:center;width:60px'],
                    ],
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
                        'headerOptions' => ['style' => 'width:70px'],
                        'contentOptions' => ['style' => 'text-align:center;color:#666666'],
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
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
                                    'class' => ' ',
                                    'style' => 'color:#666666',
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
                                    'class' => (count($model->courseAttribute) > 0 ? 
                                        'disabled' : ''),
                                    'style' => 'color:#666666',
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
</div>
<?php
    TreegridAssets::register($this);
    
    $js = <<<JS
        $('.table').treegrid({
            //initialState: 'collapsed',
        });
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>