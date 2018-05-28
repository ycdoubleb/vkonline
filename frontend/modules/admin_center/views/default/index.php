<?php

use common\models\vk\Customer;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Customer */

?>

<div class="admin_center-default-index main">
    <div class="frame">
        <div class="page-title">概况</div>
        <!--基本信息-->
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'),
                    'Info' => Yii::t('app', 'Info'),
                ]) ?></span>
            </div>
            <?= DetailView::widget([
                'model' => $model,
                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                'attributes' => [
                    'name',
                    'domain',
                    [
                        'attribute' => 'logo',
                        'format' => 'raw',
                        'value' => Html::img(WEB_ROOT . $model->logo),
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
                ],
            ]) ?>
        </div>
        <!--管理员信息-->
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', 'Administrators') ?><front class="admin-num">（3/<?= count($customerAdmin)?>）</front></span>
                <div class="framebtn">
                    <?= Html::a(Yii::t('app', 'Add'),
                            ['create-admin', 'id' => $model->id], 
                            ['id' => 'add-admin','class' => 'btn btn-success btn-flat',
                            'onclick'=>'return showElemModal($(this));'])
                    ?>
                </div>
            </div>
            <div id="admin">
                <center>加载中...</center>
            </div>
        </div>
        <!--储存信息-->
        <div class="frame-content">
            <div class="frame-title">
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
                                (((100 - floor($usedSpace['size'] / $model->good->data *100)) > 10) ? '<span style="color:#33CC00"> 充足</span>' : 
                                    '<span style="color:red"> 不足</span>') .'）</span>' : null,
                    ],
                ],
            ]) ?>   
        </div>
         <!--邀请码-->
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Signup}{User}',['Signup' => Yii::t('app', 'Signup'), 'User' => Yii::t('app', 'User')]) ?></span>
                <div class="framebtn">
                    <?= Html::button(Yii::t('app', '{Produce}{Invite Code}',['Produce' => Yii::t('app', 'Produce'),
                        'Invite Code' => Yii::t('app', 'Invite Code')]),
                            ['id' => 'createInviteCode', 'class' => 'btn btn-success btn-flat',
                                'onclick'=>'return inviteCode($(this));'])
                    ?>
                    <?= Html::button(Yii::t('app', '{Copy}{Invitation Link}',['Copy' => Yii::t('app', 'Copy'),
                        'Invitation Link' => Yii::t('app', 'Invitation Link')]),
                            ['id' => 'copyBtn', 'class' => 'btn btn-primary btn-flat',
                                'onclick'=>'return jsCopy($(this));'])
                    ?>
                </div>
            </div>
            <div id="signup-user">
                <center>加载中...</center>
            </div>
        </div>
        <!--资源统计-->
        <div class="frame-content">
            <div class="frame-title">
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
                                'color' => '#999999',
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
    </div>
</div>
<?= $this->render('/layouts/model') ?>

<?php
$admin = Url::to(['admin-index', 'id' => $model->id]);
$logIndex = Url::to(['log-index', 'id' => $model->id]);
$signupIndex = Url::to(['invite-code-index', 'id' => $model->id]);
$WEB_ROOT = WEB_ROOT;

$js = 
<<<JS
    //加载管理员列表
    $("#admin").load("$admin"); 
        
    //加载邀请码列表
    $("#signup-user").load("$signupIndex"); 
    
    /** 显示模态框 */
    window.showElemModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    };
        
    /** 生成邀请码 */
    window.inviteCode = function inviteCode() {
        $.post("/admin_center/default/create-invite-code?id=$model->id",function(data){
            if(data == '200'){
                $("#signup-user").load("$signupIndex"); 
            }
        });
    }
        
    /** 复制邀请码 */
    window.jsCopy = function jsCopy() {
        var e=document.getElementById("inviteCode");//对象是inviteCode
        e.value = "$WEB_ROOT/site/signup?code="+e.value;
        e.select();                         //选择复制对象
        tag=document.execCommand("Copy");   //执行浏览器复制命令
        if(tag){
          alert('复制邀请链接成功');
        }
    }; 
        
JS;
    $this->registerJs($js,  View::POS_READY);
    ModuleAssets::register($this);
?>