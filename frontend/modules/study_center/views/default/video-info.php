<?php

use common\components\aliyuncs\Aliyun;
use common\utils\StringUtil;
use FFMpeg\Media\Video;
use frontend\modules\study_center\assets\VideoInfoAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Video */
$this->title = $model['name'];

VideoInfoAssets::register($this);

?>

<div class="video-info main">
    <div class="video-title">
        <span class="title-name"><?= $model['name'] ?></span>
    </div>
    <div class="player">
        <video id="myVideo" src="<?= $model['path'] ?>" controls poster="<?= $model['img'] ?>" width="100%" height="500"></video>
    </div>
    <!--主讲老师+相关课程-->
    <div class="left-box">
        <div class="panel">
            <div class="panel-head">主讲老师</div>
            <div class="panel-body">
                <div class="info">
                    <?= Html::beginTag('a', ['href' => Url::to(['/teacher/default/view', 'id' => $model['teacher_id']]), 'target' => '_blank']) ?>
                    <?= Html::img($model['avatar'], ['class' => 'img-circle', 'width' => 120, 'height' => 120]) ?>
                    <p class="name"><?= $model['teacher_name'] ?></p>
                    <p class="job-title"><?= $model['teacher_des'] ?></p>
                    <?= Html::endTag('a') ?>
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head">关联课程</div>
            <div class="panel-body">
                <ul>
                    <?php foreach ($dataProvider->allModels as $index => $data): ?>
                        <li class="<?= $index % 3 == 2 ? 'clear-margin' : '' ?>">
                            <div class="pic">
                                <a href="/course/default/view?id=<?= $data['id'] ?>" title="<?= $data['course_name'] ?>" target="_blank">
                                    <img src="<?= Aliyun::absolutePath(!empty($data['cover_img']) ? $data['cover_img'] : 'static/imgs/notfound.png') ?>" width="100%" height="100%" />
                                    <p class="single-clamp course-name"><?= $data['course_name'] ?></p>
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <!--视频信息-->
    <div class="right-box">
        <div class="panel">
            <div class="panel-head">视频信息</div>
        </div>
        <div class="info">
            <?= DetailView::widget([
                'model' => $model,
                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                'attributes' => [
                    [
                        'label' => Yii::t('app', '{Video}{Name}', [
                            'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
                        ]),
                        'value' => $model['name'],
                    ],
                    [
                        'label' => Yii::t('app', 'Tag'),
                        'value' => $model['tags'],
                    ],
                    [
                        'label' => Yii::t('app', 'Created By'),
                        'value' => $model['nickname'],
                    ],
                    [
                        'label' => Yii::t('app', '{Video}{Des}', [
                            'Video' => Yii::t('app', 'Video'), 'Des' => Yii::t('app', 'Des')
                        ]),
                        'format' => 'raw',
                        'value' => $model['video_des'],
                    ],
                ]
            ])?>
        </div>
    </div>
</div>

<?php

$js = 
<<<JS
        
    //鼠标经过、离开事件
    hoverEvent();        
                
    //经过、离开事件
    function hoverEvent(){
        $(".right-box .list > ul > li").each(function(){
            var elem = $(this);
            elem.hover(function(){
                elem.addClass('hover');
                
            },function(){
                elem.removeClass('hover');
            });    
        });
    }  
JS;
    $this->registerJs($js,  View::POS_READY);
?>