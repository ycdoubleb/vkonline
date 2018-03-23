<?php

use common\models\vk\Course;
use frontend\modules\course\assets\MainAssets;
use frontend\modules\course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $model Course */


MainAssets::register($this);
ModuleAssets::register($this);

?>

<header class="header">
    <img src="/imgs/build_course/images/u5303.png" />
</header>

<div class="content">

    <div class="course-default-index main">

       <?php $form = ActiveForm::begin(['id' => 'course-form','action' => ['index'],'method' => 'get']); ?>

        <div class="col-xs-12 search-frame">
            <label class="col-lg-1 col-md-1 form-label">
                <?= Yii::t('app', '{Course}{Category}', ['Course' => Yii::t('app', 'Course'), 'Category' => Yii::t('app', 'Category')]) ?>：
            </label>
            <div class="col-lg-11 col-md-11 remove">
                <div class="search-drop-downList">
                    <?= Select2::widget(['name' => 'category_id', 'data' => [], 
                        'value' => ArrayHelper::getValue($filters, 'category_id'),
                        'options' => ['class' => 'form-control', 'placeholder' => '请选择 课程分类'],
                        'pluginOptions' => ['allowClear' => true],
                        'pluginEvents' => ['change' => 'function(){ selectLog($(this));}']
                    ]) ?>
                </div>
            </div>
        </div>
        
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
                    <?= Html::textInput('keyword', ArrayHelper::getValue($filters, 'keyword'), ['class' => 'form-control','placeholder' => '请输入 课程名称或者关键字']); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-12 col-md-12 search-frame">
            <label class="col-lg-1 col-md-1 form-label"></label>
            <div class="col-lg-1 col-md-1 remove">
                <?= Html::a('<i class="fa fa-search"></i>&nbsp;'.Yii::t('app', '{Search}{Course}', ['Search' => Yii::t('app', 'Search'), 'Course' => Yii::t('app', 'Course')]), 
                        'javascript:;', ['id' => 'submit', 'class' => 'btn btn-success']); ?>
            </div>
            <div class="col-lg-3 col-md-3" style="padding: 6px 15px">
                <?= Html::radioList('level', ArrayHelper::getValue($filters, 'level', Course::INTRANET_LEVEL), [1 => '内网', '1,2' => '全网'], [
                    'itemOptions' => [
                        'labelOptions' => [
                            'style' => 'margin-right: 30px; margin-bottom: 0'
                        ],
                    ]
                ]); ?>
            </div>
        </div>
        
        <?php ActiveForm::end(); ?>

        <div class="filter">
            <div class="result">
                <i class="fa fa-list"></i>
                <span>搜索结果</span>
            </div>
        </div>
        
        <div class="list">
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <div class="item <?= $index % 4 == 3 ? 'item-right' : null ?>">
                <div class="pic">
                    <div class="title">
                        <span><?= $model['name'] ?></span>
                    </div>
                    <div class="float"> 
                        <span><?= isset($model['fav_num']) ? $model['fav_num'] : 0 ?><i class="fa fa-star"></i></span>
                        <span><?= isset($model['zan_num']) ? $model['zan_num'] : 0 ?><i class="fa fa-thumbs-up"></i></span>
                    </div>
                </div>
                <div class="cont">
                    <div class="name">名称：<span><?= $model['name'] ?></span></div>
                    <div class="tuip">主讲：<span><?= $model['teacher']['name'] ?></span>
                        <span class="nodenum">环节数：<span><?= isset($model['node_num']) ? $model['node_num'] : 0 ?>&nbsp;节</span></span>
                    </div>
                    <div class="tuip">时间：<span><?= date('Y-m-d', $model['created_at']) ?></span>
                        <?= Html::a('查看课程', 'javascript:;', ['class' => 'see']) ?>
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

$js = 
<<<JS
   
    /** 提交表单 */
    $('#submit').click(function(){
        $('#course-form').submit();
    });

JS;
    $this->registerJs($js,  View::POS_READY);
?>