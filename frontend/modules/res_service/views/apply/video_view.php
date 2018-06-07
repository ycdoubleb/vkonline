<?php

use common\models\vk\searchs\CourseSearch;
use common\utils\DateUtil;
use frontend\modules\res_service\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $searchModel CourseSearch */


ModuleAssets::register($this);

?>

<div class="default-video-view main">
    <?php
        $btngroup = '';
        /**
        * $btnItems = [
        *     [
        *         name => 按钮名称，
        *         url  =>  按钮url，
        *         icon => 按钮图标
        *         options  => 按钮属性，
        *         symbol => html字符符号：&nbsp;，
        *         conditions  => 按钮显示条件，
        *         adminOptions  => 按钮管理选项，
        *     ],
        * ]
        */
        $btnItems = [
            [
                'name' => Yii::t('app', 'Preview'),
                'url' => ['/study_center/default/view', 'id' => $model->id],
                'icon' => null,
                'options' => ['class' => 'btn btn-primary btn-flat', 'target' => '_black'],
                'symbol' => '&nbsp;',
                'conditions' => true,
                'adminOptions' => true,
            ],
            [
                'name' => Yii::t('app', '{Copy}{Link}', [
                    'Copy' => Yii::t('app', 'Copy'), 'Link' => Yii::t('app', 'Link')
                ]),
                'url' => 'javascript:;',
                'icon' => null,
                'options' => ['class' => 'btn btn-success', 'onclick' => 'showModal($(this));return false;'],
                'symbol' => '&nbsp;',
                'conditions' => true,
                'adminOptions' => true,
            ],
            [
                'name' => Yii::t('app', 'Close'),
                'url' => ['close', 'id' => $model->id],
                'icon' => null,
                'options' => ['class' => 'btn btn-danger btn-flat', 'onclick' => 'showModal($(this));return false;'],
                'symbol' => '',
                'conditions' => true,
                'adminOptions' => true,
            ],
        ];
        
        foreach ($btnItems as $btn) {
            if($btn['conditions']){
                $btngroup .= Html::a($btn['icon'].$btn['symbol'].$btn['name'], $btn['url'], $btn['options']) . $btn['symbol'];
            }
        }
    ?>
    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{Video}{Detail}：', [
                'Video' => Yii::t('app', 'Video'), 'Detail' => Yii::t('app', 'Detail')
            ]) . $model->name ?>
        </span>
        <div class="btngroup"><?= $btngroup ?></div>
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
                    'label' => Yii::t('app', 'Video Visits'),
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
    <!--课程信息-->
    <div class="panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Video}{Info}',[
                    'Video' => Yii::t('app', 'Video'), 'Info' => Yii::t('app', 'Info'),
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
                    'attribute' => 'name',
                    'label' => Yii::t('app', 'Name'),
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
                    'attribute' => 'source_duration',
                    'label' => Yii::t('app', 'Long Time'),
                    'value' => DateUtil::intToTime($model->source_duration, ':', true),
                ],
                [
                    'attribute' => 'source_wh',
                    'label' => Yii::t('app', 'Resolution'),
                    'value' => $model->source_wh,
                ],
                [
                    'attribute' => 'created_at',
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
                [
                    'label' => Yii::t('app', 'Synopsis'),
                    'format' => 'raw',
                    'value' => "<div class=\"detail-des multi-line-clamp\">{$model->des}</div>",
                ],
                [
                    'label' => Yii::t('app', 'Preview'),
                    'format' => 'raw',
                    'value' => !empty($model->source_id) ? 
                        "<video src=\"{$paths['source_path']}\" controls poster=\"{$paths['img']}\"></video>" : null,
                ],
            ],
        ]) ?>
    </div>
    
</div>