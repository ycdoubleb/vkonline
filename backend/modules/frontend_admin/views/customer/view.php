<?php

use backend\modules\system_admin\assets\SystemAssets;
use common\models\vk\Customer;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Customer */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Customer}{List}',[
    'Customer' => Yii::t('app', 'Customer'),
    'List' => Yii::t('app', 'List'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="customer-view" style="height: 1100px;">
    <p>
        <?= Html::a(Yii::t('app', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Renew'), ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Disable'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Courses'), ['update', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
        <?= Html::a(Yii::t('app', 'Users'), ['update', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
    </p>
    <div class="col-md-12 col-xs-12 frame">
        <div class="col-xs-6 frame-content">
            <div class="frame-title">
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
                    'name',
                    'domain',
                    [
                        'attribute' => 'logo',
                        'format' => 'raw',
                        'value' => Html::img(WEB_ROOT . $model->logo),
                    ],
                    'address',
                    [
                        'attribute' => 'user_id',
                        'label' => Yii::t('app', 'Administrators'),
                        'value' => $customerAdmin,
                    ],
                    'good_id',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => !empty($model->status) ? '<span style="color:' . ($model->status == 10 ? 'green' : 'red') . '">' 
                                    . Customer::$statusUser[$model->status] . '</span>' : null,
                    ],
                    'expire_time',
                    'renew_time',
                    [
                        'attribute' => 'created_by',
                        'format' => 'raw',
                        'value' => $model->userName->nickname,
                    ],
                    'created_at:datetime',
                ],
            ]) ?>
        </div>
        <div class="col-xs-6 frame-content">
            <div>
                <div class="frame-title">
                    <i class="icon fa fa-users"></i>
                    <span><?= Yii::t('app', 'Administrators') ?></span>
                    <div class="framebtn">
                        <?php 
                                echo Html::a('<i class="fa fa-user-plus"></i> '.Yii::t('app', 'Add'),
                                ['course-make/create-helpman', 'course_id' => $model->id], 
                                ['id' => 'add-helpman','class' => 'btn btn-sm btn-success',
                                'onclick'=>'return showElemModal($(this));'])
                        ?>
                    </div>
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                    'attributes' => [
                         [
                            'attribute' => 'user_id',
                            'label' => $customerAdmin,
                            'value' => $customerAdmin,
                        ],
                        [
                            'attribute' => 'logo',
                            'format' => 'raw',
                            'value' => Html::img(WEB_ROOT . $model->logo),
                        ],
                    ],
                ]) ?>
            </div>
            <div>
                <div class="frame-title">
                    <i class="icon fa fa-database"></i>
                    <span><?= Yii::t('app', 'Storage') ?></span>
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                    'attributes' => [
                        [
                            'attribute' => 'logo',
                            'label' => Yii::t('app', 'Total Capacity'),
                            'format' => 'raw',
                            'value' => '',
                        ],
                        [
                            'attribute' => 'user_id',
                            'label' => Yii::t('app', '{Already}{Use}',[
                                'Already' => Yii::t('app', 'Already'),
                                'Use' => Yii::t('app', 'Use'),
                            ]),
                            'value' => '',
                        ],
                        [
                            'attribute' => 'user_id',
                            'label' => Yii::t('app', 'Surplus'),
                            'value' => '',
                        ],
                    ],
                ]) ?>   
            </div>
        </div>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    SystemAssets::register($this);
?>

