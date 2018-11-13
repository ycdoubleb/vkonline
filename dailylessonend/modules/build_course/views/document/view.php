<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\Document;
use common\models\vk\UserCategory;
use dailylessonend\assets\ClipboardAssets;
use dailylessonend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Document */

ModuleAssets::register($this);
GrowlAsset::register($this);
ClipboardAssets::register($this);

$this->title = Yii::t('app', "{Document}{Detail}：{$model->name}", [
    'Document' => Yii::t('app', 'Document'), 'Detail' => Yii::t('app', 'Detail')
]);

?>
<div class="document-view main">

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
                <?php if($model->created_by == Yii::$app->user->id || $model->userCategory->type == UserCategory::TYPE_SHARING){
                    echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], 
                        ['class' => 'btn btn-primary btn-flat']) . '&nbsp;';
                    echo Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-flat', 
                        'data' => [
                            'pjax' => 0, 
                            'confirm' => Yii::t('app', "{Are you sure}{Delete}【{$model->name}】{Document}", [
                                'Are you sure' => Yii::t('app', 'Are you sure '), 'Delete' => Yii::t('app', 'Delete'), 
                                'Document' => Yii::t('app', 'Document')
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
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => $model->name,
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
        
        <iframe src="http://eezxyl.gzedu.com/?furl=<?= Aliyun::absolutePath($model->file->oss_key) ?>" width="100%" height="700" style="border: none"></iframe>
        
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