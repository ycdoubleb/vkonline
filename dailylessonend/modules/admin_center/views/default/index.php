<?php

use common\models\vk\Customer;
use dailylessonend\assets\ClipboardAssets;
use dailylessonend\modules\admin_center\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Customer */

ModuleAssets::register($this);
ClipboardAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t('app', 'Survey');

?>

<div class="admin_center-default-index main">
    
    <!-- 页面标题 -->
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
        </div>
        
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                'name',
                'domain',
                [
                    'attribute' => 'logo',
                    'format' => 'raw',
                    'value' => Html::img($model->logo),
                ],
                'address',
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
            ],
        ]) ?>
    </div>
    
    <!--管理员信息-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', 'Administrators') ?>
                <front class="admin-num">（3/<span id="number"><?= count($customerAdmin)?></span>）</front>
            </span>
            <div class="btngroup pull-right">
                <?= Html::a(Yii::t('app', 'Add'), ['create-admin', 'id' => $model->id], [
                    'id' => 'add_admin','class' => 'btn btn-success btn-flat', 
                    'onclick' => 'showModal($(this).attr("href"));return false;'
                ])?>
            </div>
        </div>
        <div id="admin_info" class="set-padding">
            <!--加载-->
            <div class="loading-box">
                <span class="loading"></span>
            </div>
        </div>
    </div>
    
    <!--储存信息-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', 'Storage') ?>
            </span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
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
                    'value' => (!empty($usedSpace) && !empty($model->good->data)) ? Yii::$app->formatter->asShortSize($usedSpace) . 
                        '<span style="color:#929292">（'. sprintf("%.2f", ($usedSpace / $model->good->data)*100).' %）</span>' : null,
                ],
                [
                    'label' => Yii::t('app', 'Surplus'),
                    'format' => 'raw',
                    'value' => (!empty($usedSpace) && !empty($model->good->data)) ? Yii::$app->formatter->asShortSize($model->good->data - $usedSpace) .
                        '<span style="color:#929292">（' . sprintf("%.2f", ($model->good->data - $usedSpace) / $model->good->data * 100) . ' % '.
                            (((100 - floor($usedSpace / $model->good->data *100)) > 10) ? '<span style="color:#33CC00"> 充足</span>' : 
                                '<span style="color:red"> 不足</span>') .'）</span>' : null,
                ],
            ],
        ]) ?>   
    </div>
    
    <!--邀请码-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Signup}{User}',[
                    'Signup' => Yii::t('app', 'Signup'), 'User' => Yii::t('app', 'User')
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php  
                    //生成邀请码
                    echo Html::button(Yii::t('app', '{Produce}{Invite Code}',[
                        'Produce' => Yii::t('app', 'Produce'), 'Invite Code' => Yii::t('app', 'Invite Code')
                    ]), [
                        'id' => 'createInviteCode', 'class' => 'btn btn-success btn-flat',
                        'onclick'=>'return inviteCode($(this));'
                    ]) . '&nbsp;';
                    //复制邀请码
                    echo Html::button(Yii::t('app', '{Copy}{Invitation Link}',[
                        'Copy' => Yii::t('app', 'Copy'), 'Invitation Link' => Yii::t('app', 'Invitation Link')
                    ]), [
                        'id' => 'copyBtn', 'class' => 'btn btn-primary btn-flat',
                        'onclick'=>'return jsCopy($(this));'
                    ]);
                ?>
            </div>
        </div>
        <div id="signup_user">
            <!--加载-->
            <div class="loading-box">
                <span class="loading"></span>
            </div>
        </div>
    </div>
    
    <!--资源统计-->
<!--    <div class="vk-panel set-bottom">
        <div class="title">
            <span>
                <?php // Yii::t('app', '{Resources}{Statistics}',[
//                    'Resources' => Yii::t('app', 'Resources'), 'Statistics' => Yii::t('app', 'Statistics'),
//                ]) ?>
            </span>
        </div>
        
        <div class="set-padding">
        <?php // GridView::widget([
//            'dataProvider' => new ArrayDataProvider([
//                'allModels' => $resourceData,
//                'pagination' => FALSE,
//            ]),
//            'tableOptions' => ['class' => 'table table-bordered vk-table'],
//            'layout' => "{items}",
//            'columns' => [
//                [
//                    'label' => '',
//                    'value' => function ($data){
//                        return $data['name'];
//                    },
//                    'headerOptions' => [
//                        'style' => [
//                            'text-align' => 'center',
//                            'width' => '130px'
//                        ],
//                    ],
//                    'contentOptions' => [
//                        'style' => [
//                            'color' => '#999999',
//                            'text-align' => 'center',
//                        ],
//                    ],
//                ],
//                [
//                    'label' => Yii::t('app', 'Course'),
//                    'format' => 'raw',
//                    'value' => function ($data){
//                        return isset($data['cour_num']) ? $data['cour_num'] : null;
//                    },
//                    'headerOptions' => [
//                        'style' => [
//                            'text-align' => 'center',
//                        ],
//                    ],
//                    'contentOptions' => [
//                        'style' => [
//                            'text-align' => 'center',
//                        ],
//                    ],
//                ],
//                [
//                    'label' => Yii::t('app', 'Video'),
//                    'format' => 'raw',
//                    'value' => function ($data){
//                        return isset($data['node_num']) ? $data['node_num'] : null;
//                    },
//                    'headerOptions' => [
//                        'style' => [
//                            'text-align' => 'center',
//                        ],
//                    ],
//                    'contentOptions' => [
//                        'style' => [
//                            'text-align' => 'center',
//                        ],
//                    ],
//                ],
//                [
//                    'label' => Yii::t('app', '{Video}{Play}',[
//                        'Video' => Yii::t('app', 'Video'),
//                        'Play' => Yii::t('app', 'Play'),
//                    ]),
//                    'format' => 'raw',
//                    'value' => function ($data){
//                        return isset($data['play_count']) ? $data['play_count'] : null;
//                    },
//                    'headerOptions' => [
//                        'style' => [
//                            'text-align' => 'center',
//                        ],
//                    ],
//                    'contentOptions' => [
//                        'style' => [
//                            'text-align' => 'center',
//                        ],
//                    ],
//                ],
//            ]
//        ])?>
        </div>    
   </div>-->
        
</div>

<?php
$adminCount = count($customerAdmin);    //管理员人数

$js = <<<JS
    //加载管理员列表
    $("#admin_info").load("../default/admin-index?id={$model->id}"); 
    //加载邀请码列表
    $("#signup_user").load("../default/invite-code-index?id={$model->id}"); 
    
    /**
     * 生成邀请码
     */
    window.inviteCode = function inviteCode() {
        $.post("/admin_center/default/create-invite-code?id={$model->id}",function(data){
            if(data == '200'){
                $("#signup_user").load("../default/invite-code-index?id={$model->id}"); 
            }
        });
    }
                
    /** 
     * 复制邀请码 
     */
    window.jsCopy = function jsCopy() {
        var e = document.getElementById("inviteCode");//对象是inviteCode
        e.select();                         //选择复制对象
        tag = document.execCommand("Copy");   //执行浏览器复制命令
        if(tag){
          $.notify({
                message: '复制邀请链接成功',
            },{
                type: "success",
            });
        }else{
            $.notify({
                message: '复制邀请链接失败',
            },{
                type: "danger",
            });
        }
    };
JS;
    $this->registerJs($js,  View::POS_READY);
?>