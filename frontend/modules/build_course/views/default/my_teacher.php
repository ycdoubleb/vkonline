<?php

use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */


ModuleAssets::register($this);

$allCourse = ['第一章','第一章','第一章','第一章','第一章','第一章'];
?>

<div class="default-myVideo main">
    
    <?php $form = ActiveForm::begin(['id' => 'build-course-form']); ?>
    
    <div class="col-xs-12 search-frame"> 
        <div class="search-drop-downList">
            <?= Html::dropDownList('course_id', null, $allCourse, ['class' => 'form-control']); ?>
        </div>
    </div>
    
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
        <div class="item">
            <div class="pic">
                <div class="title">
                    <span>毕业实习（机本）</span>
                </div>
                <div class="float">
                    <span>11:25</span>
                </div>
            </div>
            <div class="cont">
                <div class="name">名称：<span>毕业实习（机本）</span></div>
                <div class="tuip">主讲：<span>何千于</span><a href="javascript:;" class="play"><i class="fa fa-play-circle"></i></a></div>
                <div class="tuip">标签：<div class="labels"><span>毕业设计（机本）</span><span>实习</span><span>机电</span></div></div>
            </div>
        </div>
        <div class="item">
            <div class="pic">
                <div class="title">
                    <span>毕业实习（机本）</span>
                </div>
                <div class="float">
                    <span>11:25</span>
                </div>
            </div>
            <div class="cont">
                <div class="name">名称：<span>毕业实习（机本）</span></div>
                <div class="tuip">主讲：<span>何千于</span><a href="javascript:;" class="play"><i class="fa fa-play-circle"></i></a></div>
                <div class="tuip">标签：<div class="labels"><span>毕业设计（机本）</span><span>实习</span><span>机电</span></div></div>
            </div>
        </div>
        <div class="item item-right">
            <div class="pic">
                <div class="title">
                    <span>毕业实习（机本）</span>
                </div>
                <div class="float">
                    <span>11:25</span>
                </div>
            </div>
            <div class="cont">
                <div class="name">名称：<span>毕业实习（机本）</span></div>
                <div class="tuip">主讲：<span>何千于</span><a href="javascript:;" class="play"><i class="fa fa-play-circle"></i></a></div>
                <div class="tuip">标签：<div class="labels"><span>毕业设计（机本）</span><span>实习</span><span>机电</span></div></div>
            </div>
        </div>
        
    </div>
    
</div>
