<?php

use common\models\vk\CustomerWatermark;
use common\widgets\watermark\WatermarkAsset;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model CustomerWatermark */

ModuleAssets::register($this);
WatermarkAsset::register($this);

$this->title = Yii::t('app', "{Watermark}{Detail}：{$model->name}", [
    'Watermark' => Yii::t('app', 'Watermark'), 'Detail' => Yii::t('app', 'Detail')
]);

?>
<div class="customer-watermark-view main">
    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!--基本信息-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php 
                    echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], 
                        ['class' => 'btn btn-primary btn-flat']) . '&nbsp;';
                    echo Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-flat', 
                        'data' => [
                            'pjax' => 0, 
                            'confirm' => Yii::t('app', "{Are you sure}{Delete}【{$model->name}】{Watermark}", [
                                'Are you sure' => Yii::t('app', 'Are you sure '), 'Delete' => Yii::t('app', 'Delete'), 
                                'Watermark' => Yii::t('app', 'Watermark')
                            ]),
                            'method' => 'post',
                        ],
                    ]);
                ?>
            </div>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'customer_id',
                    'label' => Yii::t('app', '{The}{Customer}', [
                        'The' => Yii::t('app', 'The'), 'Customer' => Yii::t('app', 'Customer')
                    ]),
                    'value' => !empty($model->customer_id) ? $model->customer->name : null,
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', '{Watermark}{Name}', [
                        'Watermark' => Yii::t('app', 'Watermark'), 'Name' => Yii::t('app', 'Name')
                    ]),
                ],
                [
                    'attribute' => 'refer_pos',
                    'label' => Yii::t('app', '{Watermark}{Position}', [
                        'Watermark' => Yii::t('app', 'Watermark'), 'Position' => Yii::t('app', 'Position')
                    ]),
                    'value' => CustomerWatermark::$referPosMap[$model->refer_pos],
                ],
                'width',
                'height',
                [
                    'attribute' => 'dx',
                    'label' => Yii::t('app', '{Level}{Shifting}', [
                        'Level' => Yii::t('app', 'Level'), 'Shifting' => Yii::t('app', 'Shifting')
                    ]),
                ],
                [
                    'attribute' => 'dy',
                    'label' => Yii::t('app', '{Vertical}{Shifting}', [
                        'Vertical' => Yii::t('app', 'Vertical'), 'Shifting' => Yii::t('app', 'Shifting')
                    ]),
                ],
                [
                    'attribute' => 'is_del',
                    'label' => Yii::t('app', 'Status'),
                    'value' => $model->is_del ? '启用' : '停用'
                ],
                [
                    'attribute' => 'is_selected',
                    'label' => Yii::t('app', '{Default}{Selected}', [
                        'Default' => Yii::t('app', 'Default'), 'Selected' => Yii::t('app', 'Selected')
                    ]),
                   'value' => $model->is_selected ? '是' : '否'
                ],
                [
                    'attribute' => 'created_at',
                    'format' => 'raw',
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
                [
                    'attribute' => 'updated_at',
                    'format' => 'raw',
                    'value' => date('Y-m-d H:i', $model->updated_at),
                ],
                [
                    'label' => Yii::t('app', 'Preview'),
                    'format' => 'raw',
                    'value' => '<div id="preview" class="preview"></div>',
                ],
            ],
        ]) ?>
    </div>
</div>

<?php
$path = !empty($model->file_id) ? $model->file->path : '';
$js = 
<<<JS
    //初始化组件
    window.watermark = new youxueba.Watermark({
        container: '#preview'
    });
    
    //添加一个水印
    window.watermark.addWatermark('vkcw',{
        refer_pos: "{$model->refer_pos}", path: "{$path}",
        width: "{$model->width}", height: "{$model->height}",
        shifting_X: "{$model->dx}", shifting_Y: "{$model->dy}"
    });
JS;
    $this->registerJs($js,  View::POS_READY);
?>