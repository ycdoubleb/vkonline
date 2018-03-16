<?php

use backend\modules\system_admin\assets\SystemAssets;
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
        <?= Html::a(Yii::t('app', 'Renew'), ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= ($model->status == 0) ? Html::a(Yii::t('app', 'Enable'), ['enable', 'id' => $model->id], [
            'class' => 'btn btn-info',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to enable this customer?'),
                'method' => 'post',
            ],
        ]) : Html::a(Yii::t('app', 'Disabled'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to disable this customer?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Courses'), ['/frontend_admin/course/index', ['customer_id' => $model->id]], ['class' => 'btn btn-default']) ?>
        <?= Html::a(Yii::t('app', 'Users'), ['/frontend_admin/user/index', ['customer_id' => $model->id]], ['class' => 'btn btn-default']) ?>
    </p>
    <div class="frame">
        <!--左侧-基本信息-->
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
                <div id="help-man">
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
                    'value' => function ($data){
                        return $data['course_num'];
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
                    'value' => function ($data){
                        return $data['video_num'];
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
                    'value' => function ($data){
                        return $data['play_count'];
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
        <?= GridView::widget([
            'dataProvider' => $recordData,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
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
                    'label' => Yii::t('app', 'Title'),
                    'value' => function ($data){
                        return $data['title'];
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
                    'label' => Yii::t('app', 'Good ID'),
                    'value' => function ($data){
                        return $data['good_id'];
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
                    'label' => Yii::t('app', 'Content'),
                    'value' => function ($data){
                        return !empty($data['content']) ? $data['content'] : '无';
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
                    'label' => Yii::t('app', 'Start Time'),
                    'value' => function ($data){
                        return !empty($data['start_time']) ? date('Y-m-d H:i', $data['start_time']) : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'width' => '90px',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'End Time'),
                    'value' => function ($data){
                        return !empty($data['end_time']) ? date('Y-m-d H:i', $data['end_time']) : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'width' => '90px',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Created By'),
                    'value' => function ($data){
                        return $data['created_by'];
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
                    'label' => Yii::t('app', '{Operating}{Time}',[
                        'Operating' => Yii::t('app', 'Operating'),
                        'Time' => Yii::t('app', 'Time'),
                    ]),
                    'value' => function ($data){
                        return !empty($data['created_at']) ? date('Y-m-d H:i', $data['created_at']) : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'width' => '90px',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'width' => '50px',
                        ],
                    ],
                ],
            ]
        ])?>
    </div>
</div>

<?= $this->render('model') ?>

<?php
$admin = Url::to(['admin-index', 'id' => $model->id]);

$js = 
<<<JS
    //加载协作人员列表
    $("#help-man").load("$admin"); 
    
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
    SystemAssets::register($this);
?>

