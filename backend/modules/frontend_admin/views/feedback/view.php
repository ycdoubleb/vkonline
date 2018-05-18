<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\UserFeedback;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model UserFeedback */

$this->title = UserFeedback::$feedbackType[$model->type];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{User}{Feedback}',[
    'User' => Yii::t('app', 'User'), 'Feedback' => Yii::t('app', 'Feedback')
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-feedback-view customer">

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                'id',
                [
                    'attribute' => 'customer_id',
                    'value' => $model->customer->name,
                ],
                [
                    'attribute' => 'user_id',
                    'value' => $model->user->nickname,
                ],
                'contact',
                [
                    'attribute' => 'type',
                    'value' => UserFeedback::$feedbackType[$model->type],
                ],
                'content:ntext',
                [
                    'attribute' => 'is_process',
                    'format' => 'raw',
                    'value' => !empty($data['is_process']) ? '<span style="color:green">是</span>' :
                                '<span style="color:red">否</span>',
                ],
                [
                    'attribute' => 'processer_id',
                    'value' => empty($model->processer_id) ? null : $model->processer->nickname,
                ],
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>