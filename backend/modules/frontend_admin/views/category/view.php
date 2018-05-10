<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\Category;
use common\models\vk\CourseAttribute;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Category */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Course}{Category}',[
            'Course' => Yii::t('app', 'Course'),
            'Category' => Yii::t('app', 'Category'),
        ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="category-view customer">

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                'id',
                'name',
                'mobile_name',
                'level',
                [
                    'attribute' => 'path',
                    'label' => Yii::t('app', 'Parent'),
                    'value' => function ($model) {
                        $path = explode(',', $model->path);
                        if(count($path) == 2){
                            return Category::findOne(['id' => $path['1']])->name;
                        } elseif (count($path) == 3) {
                            return Category::findOne(['id' => $path['1']])->name . ' / ' . Category::findOne(['id' => $path['2']])->name;
                        } else {
                            return Category::findOne(['id' => $path['1']])->name . ' / ' . Category::findOne(['id' => $path['2']])->name . ' / ' . Category::findOne(['id' => $path['3']])->name;
                        }
                    },
                ],
                [
                    'attribute' => 'is_show',
                    'value' => $model->is_show == 1 ? '是' : '否',
                ],
                'sort_order',
                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'value' => !empty($model->image) ? Html::img(WEB_ROOT . $model->image, ['width' => '680px']) : null,
                ],
                'des:ntext',
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
    <p>
        <?= Html::a(Yii::t(null, '{Create}{Course}{Attribute}', [
            'Create' => Yii::t('app', 'Create'),
            'Course' => Yii::t('app', 'Course'),
            'Attribute' => Yii::t('app', 'Attribute'),
        ]), ['attribute/create', 'category_id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-list-ul"></i>
            <span><?= Yii::t('app', 'List') ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider(['models' => $model->courseAttribute]),
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'contentOptions'=>['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'name',
                    'contentOptions'=>['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'type',
                    'value' => function ($model){
                        return CourseAttribute::$type_keys[$model->type];
                    },
                    'contentOptions'=>['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'input_type',
                    'value' => function ($model){
                        return CourseAttribute::$input_type_keys[$model->input_type];
                    },
                    'contentOptions'=>['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'index_type',
                    'value' => function ($model) {
                        return $model->index_type == 0 ? '否' : '是';
                    },
                    'contentOptions'=>['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'values',
                    'contentOptions'=>['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'sort_order',
                    'contentOptions'=>['style' => 'text-align:center'],
                ],
                [
                    'class' => ActionColumn::class,
                    'template' => '{view} {update} {delete}',
                    'contentOptions'=>['style' => 'width:70px'],
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            $options = [
                                'title' => Yii::t('yii', 'View'),
                                'aria-label' => Yii::t('yii', 'View'),
                                'data-pjax' => '0',
                            ];
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['attribute/view', 'id' => $model->id], $options);
                        },
                        'update' => function ($url, $model, $key) {
                            $options = [
                                'title' => Yii::t('yii', 'Update'),
                                'aria-label' => Yii::t('yii', 'Update'),
                                'data-pjax' => '0',
                            ];
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['attribute/update', 'id' => $model->id], $options);
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
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>
