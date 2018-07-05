<?php

use common\models\vk\CustomerWatermark;
use common\utils\StringUtil;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model CustomerWatermark */

ModuleAssets::register($this);

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
                'customer_id',
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
                    'value' => '<div id="preview" class="preview"><img class="watermark" /></div>',
                ],
            ],
        ]) ?>
    </div>
</div>

<?php
$js = 
<<<JS
    /**
     * 预览水印图位置
     * @param object|json config
     */
    window.cw_pos = function(config){
        config = $.extend({}, config);
        //如果width不是整数，则乘底图width
        if(!Wskeee.StringUtil.isInteger(Number(config.width))){
            config.width = config.width * $("#preview").width();
        }
        //如果width为0的时候，水印图的width为底图width * 0.13
        if(Number(config.width) == 0){
            config.width = $("#preview").width() * 0.13;
        }
        //如果height不是整数，则乘底图height
        if(!Wskeee.StringUtil.isInteger(Number(config.height))){
            config.height = config.height * $("#preview").height();
        }
        //如果height为0的时候，水印图的height为底图height * 0.13
        if(Number(config.height) == 0){
            config.height = $("#preview").height() * 0.13;
        }
        $(".watermark").attr({src: Wskeee.StringUtil.completeFilePath(config.src)})     //水印图路径
        //判断水印的位置
        switch(config.refer_pos){
            case 'TopRight':
                $(".watermark").css({bottom: '', left: ''})
                $(".watermark").css({
                    top: config.shifting_Y + 'px',  right: config.shifting_X + 'px', 
                    width: config.width + 'px',  height: config.height + 'px',
                });
                break;
            case 'TopLeft':
                $(".watermark").css({bottom: '', right: ''})
                $(".watermark").css({
                    top: config.shifting_Y + 'px', left: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            case 'BottomRight':
                $(".watermark").css({top: '', left: ''});
                $(".watermark").css({
                    bottom: config.shifting_Y + 'px', right: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            case 'BottomLeft':
                $(".watermark").css({top: '', right: ''});
                $(".watermark").css({
                    bottom: config.shifting_Y + 'px', left: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
        }
    }
    cw_pos({
        refer_pos: "{$model->refer_pos}", src: "{$model->file->path}",
        width: "{$model->width}", height: "{$model->height}",
        shifting_X: "{$model->dx}", shifting_Y: "{$model->dy}"
    });
JS;
    $this->registerJs($js,  View::POS_READY);
?>