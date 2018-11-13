<?php

use common\models\vk\CourseAttribute;
use dailylessonend\modules\admin_center\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model CourseAttribute */

ModuleAssets::register($this);

$this->title = Yii::t('app', "{Attribute}{Detail}：{$model->name}",[
    'Attribute' => Yii::t('app', 'Attribute'), 'Detail' => Yii::t('app', 'Detail'),
]);

?>
<div class="course-attribute-view main">
    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!--基本信息-->
    <div class="vk-panel left-panel pull-left">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php 
                    if(!$model->is_del){
                        echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], [
                            'class' => 'btn btn-primary btn-flat'
                        ]);
                        echo '&nbsp;' . Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-flat',
                            'data' => [
                                'confirm' =>  Yii::t('app', "{Are you sure}{Delete}【{$model->name}】{Attribute}", [
                                    'Are you sure' => Yii::t('app', 'Are you sure '), 'Delete' => Yii::t('app', 'Delete'), 
                                    'Attribute' => Yii::t('app', 'Attribute')
                                ]),
                                'method' => 'post',
                            ],
                        ]);
                    }
                ?>
            </div>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
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
