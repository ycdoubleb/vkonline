<?php

use common\models\vk\Log;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Log */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Log}{Detail}', [
    'Log' => Yii::t('app', 'Log'), 'Detail' => Yii::t('app', 'Detail')
]);
  
?>
<div class="log-view main vk-modal">

    <div class="modal-dialog" style="width: 850px" role="document">
        <div class="modal-content">
            
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            
            <div class="modal-body clear-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table detail-view vk-table'],
                    'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'level',
                            'label' => Yii::t('app', 'Grade'),
                            'value' => Log::$levelMap[$model->level],
                        ],
                        'category',
                        'title',
                        [
                            'attribute' => 'created_by',
                            'label' => Yii::t('app', '{Operation}{People}', [
                                'Operation' => Yii::t('app', 'Operation'), 
                                'People' => Yii::t('app', 'People')
                            ]),
                            'value' => !empty($model->created_by) ? $model->createdBy->nickname : null,
                        ],
                        [
                            'attribute' => 'created_at',
                            'label' => Yii::t('app', '{Operation}{Time}', [
                                'Operation' => Yii::t('app', 'Operation'), 
                                'Time' => Yii::t('app', 'Time')
                            ]),
                            'value' => date('Y-m-d H:i',$model->created_at),
                        ],
                        'from',
                        [
                            'attribute' => 'content',
                            'label' => Yii::t('app', 'Detail'),
                            'format' => 'raw',
                            'value' => "<div class=\"detail-des\" style=\"line-height: 30px;\">{$model->content}</div>",
                        ],
                    ],
                ]) ?>
            </div>
            
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Close'), [
                    'class' => 'btn btn-default','data-dismiss' => 'modal', 'aria-label' => 'Close'
                ]) ?>
            </div>
       </div>
    </div>

</div>
