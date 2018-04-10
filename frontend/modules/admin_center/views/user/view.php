<?php

use common\models\User;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model User */

$this->title = Yii::t('app', '{User}{Info}',[
    'User' => Yii::t('app', 'User'),
    'Info' => Yii::t('app', 'Info'),
]);

?>
<div class="user-view main">
    <p>
        <?= Html::a('<i class="fa fa-pencil">&nbsp;</i>' . Yii::t('app', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= ($model->status == 0) ? Html::a('<i class="fa fa-check-circle">&nbsp;</i>' . Yii::t('app', 'Enable'), ['enable', 'id' => $model->id], [
            'class' => 'btn btn-success',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to enable this user?'),
                'method' => 'post',
            ],
        ]) : Html::a('<i class="fa fa-ban">&nbsp;</i>' . Yii::t('app', 'Disabled'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to disable this user?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <!--基本信息-->
    <div class="frame">
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
                [
                    'attribute' => 'customer_id',
                    'format' => 'raw',
                    'value' => !empty($model->customer_id) ? $model->customer->name : null,
                ],
                'nickname',
                'username',
                [
                    'attribute' => 'avatar',
                    'format' => 'raw',
                    'value' => Html::img(WEB_ROOT . $model->avatar, ['class' => 'img-circle', 'width' => '128px', 'height' => '128px']),
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
            ],
        ]) ?>
    </div>
    <!--建设数据-->
    <div class="frame">
        <div class="frame-title">
            <i class="icon fa fa-bar-chart"></i>
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
                        Html::a('<span class="btn btn-xs btn-default" style="float:right">'
                                . '<i class="icon fa fa-eye"></i></span>', ["/admin_center/course?CourseSearch%5Bcreated_by%5D=$model->id"]),
                ],
                [
                    'label' => Yii::t('app', 'Video'),
                    'format' => 'raw',
                    'value' => $userCouVid['video_num'] . ' 个' .
                        Html::a('<span class="btn btn-xs btn-default" style="float:right">'
                                . '<i class="icon fa fa-eye"></i></span>', ["/admin_center/video?VideoSearch%5Bcreated_by%5D=$model->id"]),
                ],
            ],
        ]) ?>
    </div>
    <!--学习数据-->
    <div class="frame">
        <div class="frame-title">
            <i class="icon fa fa-bar-chart"></i>
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
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>