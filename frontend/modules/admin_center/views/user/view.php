<?php

use common\models\User;
use common\models\vk\CustomerAdmin;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model User */

ModuleAssets::register($this);

$this->title = Yii::t('app', "{User}{Info}：{$model->nickname}",[
    'User' => Yii::t('app', 'User'), 'Info' => Yii::t('app', 'Info'),
]);

$adminModel = CustomerAdmin::find()->where(['user_id' => $model->id])->one();
$userLevel = CustomerAdmin::find()->where(['user_id' => Yii::$app->user->id])->one();   //当前用户的管理员等级

?>
<div class="user-view main">
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
                <?php
                    /**
                     * 显示条件：
                     * 1、是否为管理员
                     */
                    if($isAdmin){
                        echo Html::a(Yii::t('app', 'Edit'), ['update', 'id' => $model->id], [
                            'class' => 'btn btn-primary btn-flat']);
                        /**
                         * 启用按钮显示条件：
                         * 1、用户状态要为【禁用】
                         */
                        if($model->status == User::STATUS_STOP){
                            echo  '&nbsp;' . Html::a(Yii::t('app', 'Enable'), ['enable', 'id' => $model->id], [
                                'class' => 'btn btn-success btn-flat',
                                'data' => [
                                    'pjax' => 0, 
                                    'confirm' => Yii::t('app', "{Are you sure}{Enable}【{$model->nickname}】{User}", [
                                        'Are you sure' => Yii::t('app', 'Are you sure '), 'Enable' => Yii::t('app', 'Enable'), 
                                        'User' => Yii::t('app', 'User')
                                    ]),
                                    'method' => 'post',
                                ],
                            ]);
                        }
                        /**
                         * 禁用按钮显示条件：
                         * 1、不能禁用自己
                         * 2、用户状态要为【启用】
                         */
                        if($model->id != Yii::$app->user->id && $model->status == User::STATUS_ACTIVE){
                            echo '&nbsp;' . Html::a(Yii::t('app', 'Disabled'), ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger btn-flat',
                                'data' => [
                                    'pjax' => 0, 
                                    'confirm' => Yii::t('app', "{Are you sure}{Disabled}【{$model->nickname}】{User}", [
                                        'Are you sure' => Yii::t('app', 'Are you sure '), 'Disabled' => Yii::t('app', 'Disabled'), 
                                        'User' => Yii::t('app', 'User')
                                    ]),
                                    'method' => 'post',
                                ],
                            ]);
                        }
                    }
                ?>
            </div>
        </div>
        
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'customer_id',
                    'label' => '我的品牌',
                    'value' => implode('，', ArrayHelper::getColumn(User::getUserBrand($model->id), 'name')),
                ],
                'nickname',
                'username',
                [
                    'attribute' => 'avatar',
                    'format' => 'raw',
                    'value' => Html::img($model->avatar, ['class' => 'img-circle', 'width' => '128px', 'height' => '128px']),
                ],
                'email:email',
//                [
//                    'attribute' => 'max_store',
//                    'format' => 'raw',
//                    'value' => !empty($model->max_store) ? (Yii::$app->formatter->asShortSize($model->max_store) . 
//                        '（<span style="color:'.(($model->max_store-$usedSpace['size'] > $usedSpace['size']) ? 'green' : 'red').'">已用'. 
//                            (!empty($usedSpace['size'])? Yii::$app->formatter->asShortSize($usedSpace['size']) : ' 0' ).'</span>）') :
//                                '不限制（<span style="color:green">已用'. (!empty($usedSpace['size'])? Yii::$app->formatter->asShortSize($usedSpace['size']) : ' 0' ).'</span>）'
//                    ,
//                ],
                'des:ntext',
                [
                    'attribute' => 'created_at',
                    'value' => !empty($model->created_at) ? date('Y-m-d H:i', $model->created_at) : null,
                ],
                [
                    'attribute' => 'updated_at',
                    'value' => !empty($model->updated_at) ? date('Y-m-d H:i', $model->updated_at) : null,
                ],
            ],
        ]) ?>
    </div>    
    
    <!--建设数据-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Build}{Data}',[
                    'Build' => Yii::t('app', 'Build'), 'Data' => Yii::t('app', 'Data'),
                ]) ?>
            </span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'label' => Yii::t('app', 'Course'),
                    'format' => 'raw',
                    'value' => $userCouVid['course_num'] . ' 门' .
                        Html::a('<span style="float:right"><i class="icon fa fa-eye"></i></span>', [
                            "/admin_center/course?CourseSearch%5Bcreated_by%5D=$model->id"
                        ]),
                ],
                [
                    'label' => Yii::t('app', 'Video'),
                    'format' => 'raw',
                    'value' => $userCouVid['video_num'] . ' 个' .
                        Html::a('<span style="float:right"><i class="icon fa fa-eye"></i></span>', [
                            "/admin_center/video?VideoSearch%5Bcreated_by%5D=$model->id"
                        ]),
                ],
            ],
        ]) ?>
        
    </div>
    
    <!--学习数据-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Study}{Data}',[
                    'Study' => Yii::t('app', 'Study'), 'Data' => Yii::t('app', 'Data'),
                ]) ?>
            </span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'label' => '已学课程',
                    'format' => 'raw',
                    'value' => $courseProgress['cou_pro_num'] . ' 门',
                ],
                [
                    'label' => '关注课程',
                    'format' => 'raw',
                    'value' => $courseFavorite['cou_fav_num'] . ' 门',
                ],
                [
                    'label' => '收藏视频',
                    'format' => 'raw',
                    'value' => $videoFavorite['vid_fav_num'] . ' 个',
                ],
                [
                    'label' => '评论',
                    'format' => 'raw',
                    'value' => $courseMessage['cou_mes_num'] . ' 条',
                ],
            ],
        ]) ?>
    </div>
</div>