<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\Course;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use frontend\assets\ClipboardAssets;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Video */


ModuleAssets::register($this);
GrowlAsset::register($this);
ClipboardAssets::register($this);

$this->title = Yii::t('app', "{Video}{Detail}：{$model->name}", [
    'Video' => Yii::t('app', 'Video'), 'Detail' => Yii::t('app', 'Detail')
]);
//组装视频下关联的水印图
$watermarks = '';
foreach ($watermarksFiles as $watermark) {
    $watermarks .= Html::img($watermark['path'], ['width' => 64, 'height' => 40]) . '&nbsp;&nbsp;';
}

?>

<div class="video-view main">
    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?php 
                if(($model->created_by == Yii::$app->user->id || $model->userCategory->type == UserCategory::TYPE_SHARING) 
                    && $model->mts_status !== Video::MTS_STATUS_YES && $model->type == Video::TYPE_VIDEO){
                        switch($model->mts_status){
                            case Video::MTS_STATUS_NO :
                                $statusName = Yii::t('app', 'Transcoding');
                                $btnClass = 'btn-success';
                                break;
                            case Video::MTS_STATUS_DOING :
                                $statusName = Yii::t('app', '转码中');
                                $btnClass = 'btn-info disabled';
                                break;
                            case Video::MTS_STATUS_FAIL :
                                $statusName = Yii::t('app', 'Retry');
                                $btnClass = 'btn-danger';
                                break;
                        }
                    echo Html::a($statusName, ['transcoding', 'id' => $model->id], [
                        'class' => 'btn btn-flat ' . $btnClass, 
                        'data' => [
                            'pjax' => 0, 
                            'confirm' => Yii::t('app', "{Are you sure}{Transcoding}【{$model->name}】{Video}", [
                                'Are you sure' => Yii::t('app', 'Are you sure '), 'Transcoding' => Yii::t('app', 'Transcoding'), 
                                'Video' => Yii::t('app', 'Video')
                            ]),
                            'method' => 'post',
                        ],
                    ]);
                }
            ?>
        </div>
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
                if($model->created_by == Yii::$app->user->id || $model->userCategory->type == UserCategory::TYPE_SHARING){
                    echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], 
                        ['class' => 'btn btn-primary btn-flat']) . '&nbsp;';
                    echo Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-flat', 
                        'data' => [
                            'pjax' => 0, 
                            'confirm' => Yii::t('app', "{Are you sure}{Delete}【{$model->name}】{Video}", [
                                'Are you sure' => Yii::t('app', 'Are you sure '), 'Delete' => Yii::t('app', 'Delete'), 
                                'Video' => Yii::t('app', 'Video')
                            ]),
                            'method' => 'post',
                        ],
                    ]);
                }?>
            </div>
        </div>
        
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'id',
                    'format' => 'raw',
                    'value' => $model->id . Html::button('复制ID', [
                        'id' => 'copy_' . $model->id,
                        'class' => 'btn btn-default btn-sm',
                        'data-clipboard-text' => $model->id,
                        'style' => 'margin-left: 15px;',
                        'onclick' => 'copyVideoId($(this))'
                    ]),
                ],
                [
                    'attribute' => 'user_cat_id',
                    'label' => Yii::t('app', 'Catalog'),
                    'format' => 'raw',
                    'value' => $model->user_cat_id > 0 ? str_replace(' > ', ' / ', $model->userCategory->getFullPath()) : '根目录',
                ],
                [
                    'attribute' => 'type',
                    'label' => Yii::t('app', '{Material}{Type}', [
                        'Material' => Yii::t('app', 'Material'), 'Type' => Yii::t('app', 'Type')
                    ]),
                    'format' => 'raw',
                    'value' => isset(Video::$typeMap[$model->type]) ? Video::$typeMap[$model->type] : null,
                ],
                [
                    'attribute' => 'level',
                    'label' => Yii::t('app', '{Visible}{Range}', [
                        'Visible' => Yii::t('app', 'Visible'), 'Range' => Yii::t('app', 'Range')
                    ]),
                    'format' => 'raw',
                    'value' => Course::$levelMap[$model->level],
                ],
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => $model->name,
                ],
                [
                    'attribute' => 'teacher_id',
                    'format' => 'raw',
                    'label' => Yii::t('app', '{mainSpeak}{Teacher}', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ]),
                    'value' => !empty($model->teacher_id) ? 
                        Html::img($model->teacher->avatar, ['class' => 'img-circle', 'width' => 32, 'height' => 32]) . '&nbsp;' . $model->teacher->name : null,
                ],
                [
                    'label' => Yii::t('app', 'Des'),
                    'format' => 'raw',
                    'value' => "<div class=\"detail-des\">". str_replace(array("\r\n", "\r", "\n"), "<br/>", $model->des) ."</div>",
                ],
                [
                    'label' => Yii::t('app', 'Tag'),
                    'value' => count($model->tagRefs) > 0 ? 
                        implode('、', array_unique(ArrayHelper::getColumn(ArrayHelper::getColumn($model->tagRefs, 'tags'), 'name'))) : null,
                ],
                [
                    'attribute' => 'mts_status',
                    'value' => Video::$mtsStatusName[$model->mts_status],
                ],
                [
                    'label' => Yii::t('app', 'Watermark'),
                    'format' => 'raw',
                    'value' => !empty($model->mts_watermark_ids) ? $watermarks : null,
                ],
                [
                    'attribute' => 'created_by',
                    'format' => 'raw',
                    'value' => !empty($model->created_by) ? $model->createdBy->nickname : null,
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
            ],
        ]) ?>
    </div>
    
    <!--预览-->
    <div class="vk-panel set-bottom">
        <div class="title">
            <span>
                <?= Yii::t('app', 'Preview') ?>
            </span>
        </div>
        
        <div class="set-padding">
            
            <?php 
                if(!empty($model->file_id)){
                    switch ($model->type){
                        case Video::TYPE_VIDEO :
                            echo '<video src="' . Aliyun::absolutePath($model->file->oss_key) . '"  poster="' . $model->img . '" width="100%" controls="controls"></video>';
                            break;
                        case Video::TYPE_AUDIO :
                            echo '<audio src="'. Aliyun::absolutePath($model->file->oss_key) . '" style="width: 100%" controls="controls"></audio>';
                            break;
                        case Video::TYPE_IMAGE :
                            echo '<img src="' . Aliyun::absolutePath($model->file->oss_key) . '" width="100%" />';
                            break;
                        case Video::TYPE_DOCUMENT :
                            echo '<iframe src="http://eezxyl.gzedu.com/?furl=' . Aliyun::absolutePath($model->file->oss_key) . '" width="100%" height="700" style="border: none"></iframe>';
                            break;
                        default :
                            echo '<span class="not-set">无</span>';
                            break;
                    }
                }
            ?>            
        </div>
        
    </div>
    
<?php
$js = <<<JS
    /**
     * 点击复制视频id
     * @param {obj} _this   目标对象  
     */
    window.copyVideoId = function(_this){ 
        //如果ClipboardJS已存在，则先清除
        if(window.clipboard){
            window.clipboard.destroy();
        }
        window.clipboard = new ClipboardJS('#' + _this.attr('id'));
        clipboard.on('success', function(e) {
            $.notify({
                message: '复制成功',
            },{
                type: "success",
            });
        });
        clipboard.on('error', function(e) {
            $.notify({
                message: '复制失败',
            },{
                type: "danger",
            });
        });              
    }     
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>