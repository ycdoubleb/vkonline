<?php

use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use frontend\modules\res_service\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $searchModel CourseSearch */


ModuleAssets::register($this);

?>

<div class="default-course-view main">
    
    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{Course}{Detail}：', [
                'Course' => Yii::t('app', 'Course'), 'Detail' => Yii::t('app', 'Detail')
            ]) . $model->name ?>
        </span>
        <div class="btngroup">
            <?= Html::a(Yii::t('app', 'Preview'), ['/course/default/view', 'id' => $model->id], [
                'class' => 'btn btn-primary btn-flat', 'target' => '_black'
            ]); ?>
        </div>
    </div>
    <!--基本信息-->
    <div class="panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view '],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'label' => Yii::t('app', '{Apply}{Brand}：', [
                        'Apply' => Yii::t('app', 'Apply'), 'Brand' => Yii::t('app', 'Brand')
                    ]),
                    'value' =>  '易杨开泰',
                ],
                [
                    'label' => Yii::t('app', '{The}{orderGoods}：', [
                        'The' => Yii::t('app', 'The'), 'orderGoods' => Yii::t('app', 'Order Goods')
                    ]),
                    'value' => '2018国开春季',
                ],
                [
                    'label' => Yii::t('app', 'Status'),
                    'value' => '进行中',
                ],
                [
                    'label' => Yii::t('app', 'Read Number'),
                    'value' => 0,
                ],
                [
                    'format' => 'raw',
                    'label' => Yii::t('app', 'Applicant'),
                    'value' => '李套',
                ],
                [
                    'label' => Yii::t('app', '{Apply}{Time}', [
                        'Apply' => Yii::t('app', 'Apply'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'value' => '2018-05-29 10:00',
                ],
                [
                    'label' => Yii::t('app', '{Start}{Time}', [
                        'Start' => Yii::t('app', 'Start'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'value' => '2018-05-30 00:00',
                ],
                [
                    'label' => Yii::t('app', '{End}{Time}', [
                        'End' => Yii::t('app', 'End'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'value' => '2018-05-30 00:00',
                ],
                
            ],
        ]) ?>
    </div>
    <!--视频信息-->
    <div class="panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Course}{Info}',[
                    'Course' => Yii::t('app', 'Course'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view '],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'customer_id',
                    'label' => Yii::t('app', 'Customer'),
                    'value' => !empty($model->customer_id) ? $model->customer->name : null,
                ],
                [
                    'attribute' => 'category_id',
                    'label' => Yii::t('app', 'Category'),
                    'value' => !empty($model->category_id) ? $path : null,
                ],
                [
                    'label' => Yii::t('app', 'Attribute'),
                    'value' => count($courseAttrs) > 0 ? implode('，', $courseAttrs) : null,
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', 'Course'),
                    'value' => $model->name,
                ],
                [
                    'attribute' => 'teacher_id',
                    'format' => 'raw',
                    'label' => Yii::t('app', '{mainSpeak}{Teacher}', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ]),
                    'value' => !empty($model->teacher_id) ? 
                        Html::img([$model->teacher->avatar], ['class' => 'img-circle', 'width' => 32, 'height' => 32]) . '&nbsp;' . $model->teacher->name : null,
                ],
                [
                    'label' => Yii::t('app', 'Tag'),
                    'value' => count($model->tagRefs) > 0 ? 
                        implode('、', array_unique(ArrayHelper::getColumn(ArrayHelper::getColumn($model->tagRefs, 'tags'), 'name'))) : null,
                ],
                [
                    'attribute' => 'created_at',
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
                [
                    'label' => Yii::t('app', '{Course}{Des}', [
                        'Course' => Yii::t('app', 'Course'), 'Des' => Yii::t('app', 'Des')
                    ]),
                    'format' => 'raw',
                    'value' => "<div class=\"detail-des multi-line-clamp\">{$model->des}</div>",
                ],
            ],
        ]) ?>
    </div>
    
</div>