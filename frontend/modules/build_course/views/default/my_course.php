<?php

use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

?>

<div class="default-myCourse main">
    
    <p>
        <?= Html::a(Yii::t(null, '{Create}{Course}', [
            'Create' => Yii::t('app', 'Create'),'Course' => Yii::t('app', '微课')]), ['add-course'], [
                'class' => 'btn btn-success'
            ]) ?>
    </p>
    
    <?php $form = ActiveForm::begin(['id' => 'build-course-form']); ?>
    
    <div class="col-xs-12 search-frame"> 
        <div class="search-text-input">
            <?= Html::textInput('keyword', null, ['class' => 'form-control','placeholder' => '请输入关键字...']); ?>
        </div>
        <div class = "search-btn-frame">
            <?= Html::a('<i class="fa fa-search"></i>', 'javascript:;', ['id' => 'submit', 'style' => 'float: left;']); ?>
        </div>
    </div>
    
    <?php ActiveForm::end(); ?>
    
    <div class="list">
        <?php foreach ($dataProvider->allModels as $index => $model): ?>
        <div class="item <?= $index % 3 == 2 ? 'item-right' : null ?>">
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
                    <?= Html::a('进入制作', ['view-course', 'id' => $model['id']], ['class' => 'into']) ?>
                </div>
                <div class="tuip">环节数：<span><?= isset($model['node_num']) ? $model['node_num'] : 0 ?>节</span>
                    <?= Html::a('查看课程', 'javascript:;', ['class' => 'see']) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="page">
        <?php
//        LinkPager::widget([
//            'pagination' => '',
//            'options' => ['class' => 'pagination', 'style' => 'margin: 0px;border-radius: 0px;'],
//            'prevPageCssClass' => 'page-prev',
//            'nextPageCssClass' => 'page-next',
//            'prevPageLabel' => '<i>&lt;</i>'.Yii::t('app', 'Prev Page'),
//            'nextPageLabel' => Yii::t('app', 'Next Page').'<i>&gt;</i>',
//            'maxButtonCount' => 5,
//        ]); ?>
    </div>
    
</div>
