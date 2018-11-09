<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model User */

$this->title = Yii::t('app', '{User}{Info}',[
    'User' => Yii::t('app', 'User'),
    'Info' => Yii::t('app', 'Info'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Customer}{List}',[
    'Customer' => Yii::t('app', 'Customer'),
    'List' => Yii::t('app', 'List'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-view customer">
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
        <div class="col-md-12 col-xs-12 frame-title">
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
        <div class="col-md-12 col-xs-12 frame-title">
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
                                . '<i class="icon fa fa-eye"></i></span>', ["/frontend_admin/course?CourseSearch%5Bcreated_by%5D=$model->id"]),
                ],
                [
                    'label' => Yii::t('app', 'Video'),
                    'format' => 'raw',
                    'value' => $userCouVid['video_num'] . ' 个' .
                        Html::a('<span class="btn btn-xs btn-default" style="float:right">'
                                . '<i class="icon fa fa-eye"></i></span>', ["/frontend_admin/video?VideoSearch%5Bcreated_by%5D=$model->id"]),
                ],
            ],
        ]) ?>
    </div>
    <!--学习数据-->
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
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
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>