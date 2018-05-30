<?php

use common\models\vk\CourseAttribute;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model CourseAttribute */

$this->title = $model->name;

?>
<div class="course-attribute-view main">

    <div class="frame">
        <div class="page-title">属性详情：<?= $model->name?></div>
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'),
                    'Info' => Yii::t('app', 'Info'),
                ]) ?></span>
                <div class="framebtn">
                    <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-flat']) ?>
                    <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-flat',
                        'data' => [
                            'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
            <?= DetailView::widget([
                'model' => $model,
                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                'attributes' => [
                    'id',
                    'name',
                    [
                        'attribute' => 'category_id',
                        'label' => Yii::t('app', '{The}{Category}',[
                            'The' => Yii::t('app', 'The'),
                            'Category' => Yii::t('app', 'Category'),
                        ]),
                        'value' => function ($model) use($categoryName){
                            return $categoryName[$model->category_id];
                        },
                    ],
                    [
                        'attribute' => 'type',
                        'value' => function ($model){
                            return CourseAttribute::$type_keys[$model->type];
                        },
                    ],
                    [
                        'attribute' => 'input_type',
                        'value' => function ($model){
                            return CourseAttribute::$input_type_keys[$model->input_type];
                        },
                    ],
                    [
                        'attribute' => 'index_type',
                        'value' => function ($model){
                            return CourseAttribute::$index_type_keys[$model->index_type];
                        },
                    ],
                    'sort_order',
                    'values:ntext',
                    [
                        'attribute' => 'is_del',
                        'value' => function ($model){
                            return $model->is_del == 0 ? '否' : '是';
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>
