<?php

use common\models\vk\Course;
use common\models\vk\Video;
use common\utils\DateUtil;
use frontend\modules\video\assets\MainAssets;
use frontend\modules\video\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $model Video */


MainAssets::register($this);
ModuleAssets::register($this);

$this->title = Yii::t('app', 'Video');

?>

<header class="header">
    <?= Html::img(['/imgs/video/images/u5303.png']) ?>
</header>

<div id="tips" class="content">
    
    <div class="video-default-index main">
        
        <?php $form = ActiveForm::begin(['id' => 'video-form',
           'action' => ['result', '#' => 'tips'],'method' => 'get',
        ]); ?>

        <div class="col-lg-12 col-md-12 search-frame">
            <label class="col-lg-1 col-md-1 form-label">
                <?= Yii::t('app', '{MainSpeak}{Teacher}', ['MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')]) ?>：
            </label>
            <div class="col-lg-11 col-md-11 remove">
                <div class="search-text-input remove">
                    <?= Html::textInput('teacher_name', ArrayHelper::getValue($filters, 'teacher_name'), ['class' => 'form-control','placeholder' => '请输入 主讲老师 名称']); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-12 col-md-12 search-frame">
            <label class="col-lg-1 col-md-1 form-label"><?= Yii::t('app', 'Keyword') ?>：</label>
            <div class="col-lg-11 col-md-11 remove">
                <div class="search-text-input remove">
                    <?= Html::textInput('keyword', ArrayHelper::getValue($filters, 'keyword'), ['class' => 'form-control','placeholder' => '请输入 关键字']); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-12 col-md-12 search-frame">
            <label class="col-lg-1 col-md-1 form-label"></label>
            <div class="col-lg-1 col-md-1 remove">
                <?= Html::a('<i class="fa fa-search"></i>&nbsp;'.Yii::t('app', '{Search}{Course}', ['Search' => Yii::t('app', 'Search'), 'Course' => Yii::t('app', 'Course')]), 
                        'javascript:;', ['id' => 'submit', 'class' => 'btn btn-success']); ?>
            </div>
            <?php if(!Yii::$app->user->identity->is_official): ?>
            <div class="col-lg-3 col-md-3" style="padding: 6px 15px">
                <?= Html::radioList('level', ArrayHelper::getValue($filters, 'level', Course::INTRANET_LEVEL), [1 => '内网', 2 => '全网'], [
                    'itemOptions' => [
                        'labelOptions' => [
                            'style' => 'margin-right: 30px; margin-bottom: 0'
                        ],
                    ]
                ]); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php ActiveForm::end(); ?>

        <div class="filter">
            <div class="result">
                <i class="fa fa-list"></i>
                <span>搜索结果</span>
            </div>
            <div class="sort">
                <ul>
                    <li id="zan_count">
                        <?= Html::a('口碑', array_merge(['index'], array_merge($filters, ['sort' => 'zan_count', '#' => 'tips'])), ['id' => 'zan_count', 'data-sort' => 'zan_count']) ?>
                    </li>
                    <li id="favorite_count">
                        <?= Html::a('人气', array_merge(['index'], array_merge($filters, ['sort' => 'favorite_count', '#' => 'tips'])), ['id' => 'favorite_count', 'data-sort' => 'favorite_count']) ?>
                    </li>
                    <li id="created_at">
                        <?= Html::a('时间', array_merge(['index'], array_merge($filters, ['sort' => 'created_at', '#' => 'tips'])), ['id' => 'created_at', 'data-sort' => 'created_at']) ?>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="list">
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <div class="item <?= $index % 4 == 3 ? 'item-right' : null ?>">
                <div class="pic">
                    <?php if($model['img'] == ''): ?>
                    <div class="title">
                        <span><?= $model['name'] ?></span>
                    </div>
                    <?php else: ?>
                    <?= Html::img(['/' . $model['img']], ['width' => '100%']) ?>
                    <?php endif; ?>
                    <div class="float"> 
                        <span><?= isset($model['play_num']) ? $model['play_num'] : 0 ?><i class="fa fa-eye"></i></span>
                        <span><?= $model['favorite_count'] ?><i class="fa fa-heart"></i></span>
                        <span class="right"><?= $model['zan_count'] ?><i class="fa fa-thumbs-up"></i></span>
                    </div>
                    <div class="duration">
                        <span><?= DateUtil::intToTime($model['source_duration']) ?></span>
                    </div>
                </div>
                <div class="cont">
                    <div class="name">课程：<span><?= $model['courseNode']['course']['name'] ?></span></div>
                    <div class="tuip">名称：<span><?= $model['name'] ?></span></div>
                    <div class="tuip">主讲：<span><?= $model['teacher']['name'] ?></span>
                        <?= Html::a('<i class="fa fa-play-circle"></i>', ['/study_center/default/view', 'id' => $model['id']], ['class' => 'play']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="page center">
            <?=  LinkPager::widget([
                'pagination' => $pagers,
                'options' => ['class' => 'pagination', 'style' => 'margin: 0px;border-radius: 0px;'],
                'prevPageCssClass' => 'page-prev',
                'nextPageCssClass' => 'page-next',
                'prevPageLabel' => '<i>&lt;</i>'.Yii::t('app', 'Prev'),
                'nextPageLabel' => Yii::t('app', 'Next').'<i>&gt;</i>',
                'maxButtonCount' => 5,
            ]); ?>
        </div>

    </div>

</div>
    
<?php

$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');
$js = 
<<<JS
    $(".filter .sort ul li[id=$sort]").addClass('active');
    $(".filter .sort ul li > a[id=$sort]").addClass('desc');
        
    /** 提交表单 */
    $('#submit').click(function(){
        $('#video-form').submit();
    });

JS;
    $this->registerJs($js,  View::POS_READY);
?>