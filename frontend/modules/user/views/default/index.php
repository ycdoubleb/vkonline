<?php

use frontend\modules\user\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */

ModuleAssets::register($this);

?>

<div class="user-default-index main">
    
    <div class="frame">
        
        <div class="page-title"><span>概况</span></div>
        
        <!--基本信息-->
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'),
                    'Info' => Yii::t('app', 'Info'),
                ]) ?></span>
                <div class="framebtn">
                    <?= Html::a(Yii::t('app', 'Edit'),['update', 'id' => $model->id], 
                            ['id' => 'add-admin','class' => 'btn btn-flat btn-primary'])
                    ?>
                </div>
            </div>
            <?= DetailView::widget([
                'model' => $model,
                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                'attributes' => [
                    [
                        'attribute' => 'customer_id',
                        'label' => '我的品牌',
                        'format' => 'raw',
                        'value' => function ($model) use($userBrand){
                            $brand = '';
                            foreach ($userBrand as $value) {
                                $brand .= $value['brand_id'] == Yii::$app->user->identity->customer_id ? 
                                    '<div class="brand bingo">' . $value['name'] . '</div>' : 
                                '<div class="brand">' . $value['name'] .
                                    Html::a(' <span>x</span>', ['del-bingding', 'id' => $value['id']], [
                                        'title' => "删除绑定",
                                        'onclick' => 'showModal($(this).attr("href"));return false;'
                                    ])
                                . '</div>';
                            }
                            return $brand . Html::a('<i class="fa fa-plus-circle"></i>', 
                                    ['add-bingding', 'user_id' => $model->id], [
                                        'title' => "添加绑定",
                                        'onclick' => 'showModal($(this).attr("href"));return false;'
                                    ]);
                        },
                    ],
                    'nickname',
                    'username',
                    [
                        'attribute' => 'avatar',
                        'format' => 'raw',
                        'value' => Html::img($model->avatar, ['class' => 'img-circle', 'width' => 128, 'height' => 128]),
                    ],
                    'email:email',
//                    [
//                        'label' => '绑定第三方账号',
//                        'attribute' => 'max_store',
//                        'format' => 'raw',
//                        'value' => function() use($wechatUser, $weiboUser, $weibo_url, $qqUser) {
//                            $bingdingUser = '';
//                            if($wechatUser == null){
//                                $bingdingUser = '<a href="javascrip:;" class="wechat" title="绑定微信号"></a>';
//                            } if ($weiboUser == null) {
//                                $bingdingUser .= '<a href="'. $weibo_url .'" class="weibo" title="绑定微博账号"></a>';
//                            } if ($qqUser == null) {
//                                $bingdingUser .= '<a href="/callback/qq-callback/index" class="qq" title="绑定QQ账号"></a>';
//                            }
//                            return $bingdingUser;
//                        }
//                    ],
//                    [
//                        'attribute' => 'max_store',
//                        'format' => 'raw',
//                        'value' => !empty($model->max_store) ? (Yii::$app->formatter->asShortSize($model->max_store) . 
//                            '（<span style="color:'.(($model->max_store-$usedSpace['size'] > $usedSpace['size']) ? 'green' : 'red').'">已用'. 
//                                (!empty($usedSpace['size'])? Yii::$app->formatter->asShortSize($usedSpace['size']) : ' 0' ).'</span>）') :
//                                    '不限制（<span style="color:green">已用'. (!empty($usedSpace['size'])? Yii::$app->formatter->asShortSize($usedSpace['size']) : ' 0' ).'</span>）',
//                    ],
                    'des:ntext',
                    [
                        'attribute' => 'created_at',
                        'value' => date('Y-m-d H:i', $model->created_at),
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => date('Y-m-d H:i', $model->updated_at),
                    ]
                ],
            ]) ?>
        </div>
        
        <!--建设数据-->
        <?php if($model->type == 2): ?>
            <!--散户不显示建设数据-->
            <div class="frame-content">
                <div class="frame-title">
                    <span><?= Yii::t('app', '{Build}{Data}',[
                        'Build' => Yii::t('app', 'Build'),
                        'Data' => Yii::t('app', 'Data'),
                    ]) ?></span>
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                    'attributes' => [
                        [
                            'label' => Yii::t('app', 'Course'),
                            'format' => 'raw',
                            'value' => $userCouVid['course_num'] . ' 门' ,
//                                Html::a('<i class="icon fa fa-eye"></i></span>', ['/build_course/course/index'], [
//                                    'target' => '_blank', 'style' => 'float: right']),
                        ],
                        [
                            'label' => Yii::t('app', 'Video'),
                            'format' => 'raw',
                            'value' => $userCouVid['video_num'] . ' 个' ,
//                                Html::a('<i class="icon fa fa-eye"></i></span>', ['/build_course/video/index'], [
//                                    'target' => '_blank', 'style' => 'float: right']),
                        ],
                    ],
                ]) ?>
            </div>
        <?php endif;?>
        <!--学习数据-->
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Study}{Data}',[
                    'Study' => Yii::t('app', 'Study'),
                    'Data' => Yii::t('app', 'Data'),
                ]) ?></span>
            </div>
            <?= DetailView::widget([
                'model' => $model,
                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                'attributes' => [
                    [
                        'label' => '已学课程',
                        'format' => 'raw',
                        'value' => $courseProgress['cou_pro_num'] . ' 门',
                    ],
                    [
                        'label' => '收藏课程',
                        'format' => 'raw',
                        'value' => $courseFavorite['cou_fav_num'] . ' 门',
                    ],
                    [
                        'label' => '收藏视频',
                        'format' => 'raw',
                        'value' => $videoFavorite['vid_fav_num'] . ' 个',
                    ],
                    [
                        'label' => Yii::t('app', 'Comment'),
                        'format' => 'raw',
                        'value' => $courseMessage['cou_mes_num'] . ' 条',
                    ],
                ],
            ]) ?>
        </div>
        
    </div>

</div>
