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
    
    <!--储存空间-->
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="fa fa-bar-chart"></i>
            <span><?= Yii::t('app', '{Storage}{Space}',['Storage' => Yii::t('app', 'Storage'),'Space' => Yii::t('app', 'Space'),]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'label' => '总大小',
                    'format' => 'raw',
                    'value' => Yii::$app->formatter->asShortSize($model->max_store),
                ],
                [
                    'label' => '已用',
                    'format' => 'raw',
                    'value' => Yii::$app->formatter->asShortSize($usedSpace['size']). '（'. ($model->max_store != 0 ? sprintf('%.2f', $usedSpace['size'] / $model->max_store * 100) : 0) . '%）',
                ],
            ],
        ]) ?>
    </div>
    <!--建设数据-->
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="fa fa-cubes"></i>
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
                        Html::a('<i class="icon fa fa-eye"></i></span>', ['/build_course/default/my-course'], [
                            ' class' => 'btn btn-xs btn-default', 'style' => 'float: right']),
                ],
                [
                    'label' => Yii::t('app', 'Video'),
                    'format' => 'raw',
                    'value' => $userCouVid['video_num'] . ' 个' .
                        Html::a('<i class="icon fa fa-eye"></i></span>', ['/build_course/default/my-video'], [
                            ' class' => 'btn btn-xs btn-default', 'style' => 'float: right']),
                ],
            ],
        ]) ?>
    </div>
    <!--学习数据-->
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="fa fa-bar-chart"></i>
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
