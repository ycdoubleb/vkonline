<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\searchs\UserFeedbackSearch;
use common\models\vk\UserFeedback;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserFeedbackSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{User}{Feedback}',[
    'User' => Yii::t('app', 'User'), 'Feedback' => Yii::t('app', 'Feedback')
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-feedback-index customer">

    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-list-ul"></i>
            <span><?= Yii::t('app', 'List') ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'customer_id',
                    'label' => Yii::t('app', 'Customer'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'customer_id',
                        'data' => $feedbackCustomer,
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data) {
                        return $data['cus_name'];
                    },
                    'contentOptions' => ['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'user_id',
                    'label' => Yii::t('app', 'Feedbacker'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'user_id',
                        'data' => $feedbackUser,
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data) {
                        return $data['user_name'];
                    },
                    'contentOptions' => ['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'type',
                    'label' => Yii::t('app', '{Problem}{Type}',[
                        'Problem' => Yii::t('app', 'Problem'), 'Type' => Yii::t('app', 'Type')]),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'type',
                        'data' => UserFeedback::$feedbackType,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data) {
                        return UserFeedback::$feedbackType[$data['type']];
                    },
                    'contentOptions' => ['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'content',
                    'label' => Yii::t('app', 'Content'),
                    'value' => function ($data) {
                        return $data['content'];
                    },
                    'contentOptions' => ['style' => 'white-space:normal'],
                ],
                [
                    'attribute' => 'is_process',
                    'label' => Yii::t('app', '{Is}{Solve}',[
                        'Is' => Yii::t('app', 'Is'), 'Solve' => Yii::t('app', 'Solve')
                    ]),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'is_process',
                        'data' => ['0' => '否', '1' => '是'],
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'format' => 'raw',
                    'value' => function ($data) {
                        return !empty($data['is_process']) ? '<span style="color:green">是</span>' :
                                '<span style="color:red">否</span>';
                    },
                    'contentOptions' => ['style' => 'text-align:center'],
                ],
                [
                    'attribute' => 'processer_id',
                    'label' => Yii::t('app', 'Processer'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'processer_id',
                        'data' => $solveUser,
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data) {
                        return $data['processer'];
                    },
                    'contentOptions' => ['style' => 'text-align:center'],
                ],
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>
<?php

    $js = <<<JS

JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>