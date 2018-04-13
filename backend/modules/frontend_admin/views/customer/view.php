<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\Customer;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
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
<div class="customer-view">
    <p>
        <?= Html::a(Yii::t('app', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= ($model->status == Customer::STATUS_STOP) ?  Html::a(Yii::t('app', 'Enable'), ['renew', 'id' => $model->id], ['class' => 'btn btn-success', 'onclick'=>'return showElemModal($(this));']) :
            Html::a(($model->good_id != 0) ? Yii::t('app', 'Renew') : Yii::t('app', 'Opening'), 
                ['renew', 'id' => $model->id], ['class' => 'btn btn-success', 'onclick'=>'return showElemModal($(this));']) ?>
        <?= ($model->status == Customer::STATUS_STOP) ? '' : Html::a(Yii::t('app', 'Disable'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to disable this customer?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Courses'), ["/frontend_admin/course?CourseSearch%5Bcustomer_id%5D=$model->id"], ['class' => 'btn btn-default']) ?>
        <?= Html::a(Yii::t('app', 'Users'), ["/frontend_admin/user?UserSearch%5Bcustomer_id%5D=$model->id"], ['class' => 'btn btn-default']) ?>
    </p>
    <div class="frame">
        <!--左侧-基本信息-->
        <div class="col-xs-6 frame-content">
            <div class="frame-title">
                <i class="icon fa fa-file-text"></i>
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
                    [
                        'attribute' => 'good_id',
                        'value' => !empty($model->good_id) ? $model->good->name : null,
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => '<span style="color:' . ($model->status == 10 ? 'green' : 'red') . '">' 
                                    . Customer::$statusUser[$model->status] . '</span>',
                    ],
                    [
//                        'attribute' => 'expire_time',
                        'label' => Yii::t('app', 'Start Time'),
                        'value' => !empty($model->staEndTime->start_time) ? date('Y-m-d H:i', $model->staEndTime->start_time) : null,
                    ],
                    [
                        'attribute' => 'expire_time',
                        'label' => Yii::t('app', '{Expire}{Time}',[
                            'Expire' => Yii::t('app', 'Expire'),
                            'Time' => Yii::t('app', 'Time'),
                        ]),
                        'value' => !empty($model->expire_time) ? date('Y-m-d H:i', $model->expire_time) : null,
                    ],
                    [
                        'attribute' => 'created_by',
                        'format' => 'raw',
                        'value' => $model->userName->nickname,
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => !empty($model->created_at) ? date('Y-m-d H:i', $model->created_at) : null,
                    ]
                ],
            ]) ?>
        </div>
        <!--右侧-管理员/储存信息-->
        <div class="col-xs-6 frame-content">
            <!--管理员信息-->
            <div>
                <div class="frame-title">
                    <i class="icon fa fa-users"></i>
                    <span><?= Yii::t('app', 'Administrators') ?></span>
                    <div class="framebtn">
                        <?= Html::a('<i class="fa fa-user-plus"></i> '.Yii::t('app', 'Add'),
                                ['create-admin', 'id' => $model->id], 
                                ['id' => 'add-admin','class' => 'btn btn-sm btn-success',
                                'onclick'=>'return showElemModal($(this));'])
                        ?>
                    </div>
                </div>
                <div id="admin">
                    <center>加载中...</center>
                </div>
            </div>
            <!--储存信息-->
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
                            'label' => Yii::t('app', 'Total Capacity'),
                            'format' => 'raw',
                            'value' => !empty($model->good->data) ? Yii::$app->formatter->asShortSize($model->good->data) : null,
                        ],
                        [
                            'label' => Yii::t('app', '{Already}{Use}',[
                                'Already' => Yii::t('app', 'Already'),
                                'Use' => Yii::t('app', 'Use'),
                            ]),
                            'format' => 'raw',
                            'value' => !empty($usedSpace['size']) ? Yii::$app->formatter->asShortSize($usedSpace['size']) . 
                                '<span style="color:#929292">（'. sprintf("%.2f", ($usedSpace['size'] / $model->good->data)*100).' %）</span>' : null,
                        ],
                        [
                            'label' => Yii::t('app', 'Surplus'),
                            'format' => 'raw',
                            'value' => !empty($model->good->data) ? Yii::$app->formatter->asShortSize($model->good->data - $usedSpace['size']) .
                                '<span style="color:#929292">（' . sprintf("%.2f", ($model->good->data - $usedSpace['size']) / $model->good->data * 100) . ' % '.
                                    (((100 - floor($usedSpace['size'] / $model->good->data *100)) > 10) ? '<span style="color:green"> 充足</span>' : 
                                        '<span style="color:red"> 不足</span>') .'）</span>' : null,
                        ],
                    ],
                ]) ?>   
            </div>
        </div>
    </div>
    <!--资源统计-->
    <div class="frame">
        <div class="frame-title">
            <i class="icon fa fa-line-chart"></i>
            <span><?= Yii::t('app', '{Resources}{Statistics}',[
                'Resources' => Yii::t('app', 'Resources'),
                'Statistics' => Yii::t('app', 'Statistics'),
            ]) ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $resourceData,
                'pagination' => FALSE,
            ]),
            'layout' => "{items}",
            'columns' => [
                [
                    'label' => '',
                    'value' => function ($data){
                        return $data['name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'width' => '130px'
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Course'),
                    'format' => 'raw',
                    'value' => function ($data){
                        return isset($data['cour_num']) ? $data['cour_num'] : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Video'),
                    'format' => 'raw',
                    'value' => function ($data){
                        return isset($data['node_num']) ? $data['node_num'] : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Video}{Play}',[
                        'Video' => Yii::t('app', 'Video'),
                        'Play' => Yii::t('app', 'Play'),
                    ]),
                    'format' => 'raw',
                    'value' => function ($data){
                        return isset($data['play_count']) ? $data['play_count'] : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
            ]
        ])?>
    </div>
    
    <!--操作记录-->
    <div class="frame">
        <div class="frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Operating}{Record}',[
                'Operating' => Yii::t('app', 'Operating'),
                'Record' => Yii::t('app', 'Record'),
            ]) ?></span>
        </div>
        <div id="actLog">
            <center>加载中...</center>
        </div>
    </div>
</div>

<?= $this->render('model') ?>

<?php
$admin = Url::to(['admin-index', 'id' => $model->id]);
$logindex = Url::to(['log-index', 'id' => $model->id]);

$js = 
<<<JS
    $('.myModal').on('hide.bs.modal', function (e) {
        window.location.reload();
    })
        
    //加载管理员列表
    $("#admin").load("$admin"); 
        
    //加载管理员列表
    $("#actLog").load("$logindex"); 
    
    /** 显示模态框 */
    window.showElemModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    }
    
    
JS;
    $this->registerJs($js, View::POS_READY);
?>

<?php
    FrontendAssets::register($this);
?>

