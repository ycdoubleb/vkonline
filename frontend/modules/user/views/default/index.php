<?php

use frontend\modules\user\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */

ModuleAssets::register($this);

//Yii::$app->formatter->sizeFormatBase = 1000;

?>

<div class="user-default-index main">
    
    <div class="frame">
        
        <div class="page-title"><span>概况</span>
            <div class="framebtn">
                <?= Html::a(Yii::t('app', 'Edit'),['update', 'id' => $model->id], 
                        ['id' => 'add-admin','class' => 'btn btn-sm btn-primary',
                            'onclick'=>'return showElemModal($(this));'])
                ?>
            </div>
        </div>
        
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
//                    [
//                        'attribute' => 'customer_id',
//                        'format' => 'raw',
//                        'value' => !empty($model->customer_id) ? $model->customer->name : null,
//                    ],
                    'nickname',
                    'username',
                    [
                        'attribute' => 'avatar',
                        'format' => 'raw',
                        'value' => Html::img([$model->avatar], ['class' => 'img-circle', 'width' => 128, 'height' => 128]),
                    ],
                    'email:email',
                    [
                        'attribute' => 'max_store',
                        'format' => 'raw',
                        'value' => !empty($model->max_store) ? (Yii::$app->formatter->asShortSize($model->max_store) . 
                            '（<span style="color:'.(($model->max_store-$usedSpace['size'] > $usedSpace['size']) ? 'green' : 'red').'">已用'. 
                                (!empty($usedSpace['size'])? Yii::$app->formatter->asShortSize($usedSpace['size']) : ' 0' ).'</span>）') :
                                    '不限制（<span style="color:green">已用'. (!empty($usedSpace['size'])? Yii::$app->formatter->asShortSize($usedSpace['size']) : ' 0' ).'</span>）'
                        ,
                    ],
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
                        'value' => $userCouVid['course_num'] . ' 门' .
                            Html::a('<i class="icon fa fa-eye"></i></span>', ['/build_course/course/index'], [
                                'target' => '_blank', 'style' => 'float: right']),
                    ],
                    [
                        'label' => Yii::t('app', 'Video'),
                        'format' => 'raw',
                        'value' => $userCouVid['video_num'] . ' 个' .
                            Html::a('<i class="icon fa fa-eye"></i></span>', ['/build_course/video/index'], [
                                'target' => '_blank', 'style' => 'float: right']),
                    ],
                ],
            ]) ?>
        </div>
        
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
                        'label' => '总学习时长',
                        'format' => 'raw',
                        'value' => Yii::$app->formatter->asDuration($studyTime['study_time']),
                    ],
                    [
                        'label' => '已学课程',
                        'format' => 'raw',
                        'value' => $courseProgress['cou_pro_num'] . ' 门',
                    ],
                    [
                        'label' => '已学环节',
                        'format' => 'raw',
                        'value' => $videoProgress['vid_pro_num'] . ' 个',
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

</div>
