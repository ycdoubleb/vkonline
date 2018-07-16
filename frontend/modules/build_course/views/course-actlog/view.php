<?php

use common\models\vk\CourseActLog;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model CourseActLog */


ModuleAssets::register($this);

$this->title = Yii::t('app', '{Operation}{Log}{Detail}', [
    'Operation' => Yii::t('app', 'Operation'), 'Log' => Yii::t('app', 'Log'),
    'Detail' => Yii::t('app', 'Detail')
]);

?>
<div class="coutse_actlog-view main vk-modal">

    <div class="modal-dialog" role="document">
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
                    'options' => ['class' => 'table table-bordered detail-view vk-table', 'style' => 'margin-top: 1px;'],
                    'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
                    'attributes' => [
                        'action',
                        'title',
                        [
                            'attribute' => 'content',
                            'format' => 'raw',
                            'value' => implode("<br/>",explode("\n\r", $model->content)),
                        ],
                        [
                            'attribute' => 'created_by',
                            'value' => !empty($model->created_by) ? $model->createdBy->nickname : null,
                        ],
                        [
                            'attribute' => 'course_id',
                            'value' => !empty($model->course_id) ? $model->course->name : null,
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => date('Y-m-d H:i',$model->created_at),
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => date('Y-m-d H:i',$model->updated_at),
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