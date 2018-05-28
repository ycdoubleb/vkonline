<?php

use common\models\vk\Category;
use common\models\vk\CourseAttribute;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Category */

?>
<div class="category-view main">

    <div class="frame">
        <div class="page-title">分类详情：<?= $model->name?>
            <span>
                <?= Html::a(Yii::t('app', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-flat']) ?>
                <?= count($model->courseAttribute) > 0 ? '' : Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-flat',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ])?>
            </span>
        </div>
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'),
                    'Info' => Yii::t('app', 'Info'),
                ]) ?></span>
            </div>
            <?= DetailView::widget([
                'model' => $model,
                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                'attributes' => [
                    'name',
                    'mobile_name',
                    [
                        'attribute' => 'path',
                        'label' => Yii::t('app', 'Parent'),
                        'value' => !empty($model->path) ? $path : null,
                    ],
                    [
                        'attribute' => 'is_show',
                        'value' => $model->is_show == 1 ? '是' : '否',
                    ],
                    'sort_order',
                    [
                        'attribute' => 'created_at',
                        'value' => !empty($model->created_at) ? date('Y-m-d H:i', $model->created_at) : null,
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => !empty($model->updated_at) ? date('Y-m-d H:i', $model->updated_at) : null,
                    ],
                ],
            ]) ?>
        </div>
    </div>
 
    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', 'Attribute') ?></span>
                <div class="framebtn">
                    <?= Html::a(Yii::t('app', 'Add'), ['attribute/create', 'category_id' => $model->id],
                            ['class' => 'btn btn-success btn-flat']) ?>
                </div>
            </div>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n{summary}\n{pager}",
                'columns' => [
                    [
                        'attribute' => 'name',
                        'label' => Yii::t('app', 'Name'),
                        'headerOptions' => ['style' => 'width:100px'],
                    ],
                    [
                        'attribute' => 'type',
                        'label' => Yii::t('app', 'Type'),
                        'value' => function ($model){
                            return CourseAttribute::$type_keys[$model->type];
                        },
                        'headerOptions' => ['style' => 'width:100px'],
                    ],
                    [
                        'attribute' => 'input_type',
                        'label' => Yii::t('app', '{Input}{Type}',['Input' => Yii::t('app', 'Input'),'Type' => Yii::t('app', 'Type'),]),
                        'value' => function ($model){
                            return CourseAttribute::$input_type_keys[$model->input_type];
                        },
                        'headerOptions' => ['style' => 'width:100px'],
                    ],
                    [
                        'attribute' => 'index_type',
                        'label' => Yii::t('app', '{Is}{Screen}',['Is' => Yii::t('app', 'Is'),'Screen' => Yii::t('app', 'Screen'),]),
                        'value' => function ($model) {
                            return $model->index_type == 0 ? '否' : '是';
                        },
                        'headerOptions' => ['style' => 'width:80px'],
                    ],
                    [
                        'attribute' => 'values',
                        'label' => Yii::t('app', 'Values'),
                    ],
                    [
                        'attribute' => 'sort_order',
                        'label' => Yii::t('app', 'Sort Order'),
                        'headerOptions' => ['style' => 'width:60px'],
                    ],
                    [
                        'class' => ActionColumn::class,
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                $options = [
                                    'title' => Yii::t('yii', 'View'),
                                    'aria-label' => Yii::t('yii', 'View'),
                                    'data-pjax' => '0',
                                ];
                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['attribute/view', 'id' => $model->id], $options).' ';
                            },
                            'update' => function ($url, $model, $key) {
                                $options = [
                                    'title' => Yii::t('yii', 'Update'),
                                    'aria-label' => Yii::t('yii', 'Update'),
                                    'data-pjax' => '0',
                                ];
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['attribute/update', 'id' => $model->id], $options).' ';
                            },
                            'delete' => function ($url, $model, $key) {
                                $options = [
                                    'title' => Yii::t('yii', 'Delete'),
                                    'aria-label' => Yii::t('yii', 'Delete'),
                                    'data-pjax' => '0',
                                    'data-method' => 'post'
                                ];
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['attribute/delete', 'id' => $model->id], $options);
                            }
                        ],
                        'headerOptions' => ['style' => 'width:80px'],
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
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>
