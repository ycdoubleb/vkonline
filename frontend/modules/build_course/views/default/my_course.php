<?php

use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */


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
        
        <div class="item">
            <div class="pic">
                <div class="title">
                    <span>毕业实习（机本）</span>
                </div>
<!--                <div class="avatars">
                    <?= Html::img(['/resources/build_course/images/u113.png'], ['width' => '100%']) ?>
                </div>-->
                <div class="float">
                    <span>9855<i class="fa fa-star"></i></span>
                    <span>868<i class="fa fa-thumbs-up"></i></span>
                </div>
            </div>
            <div class="cont">
                <div class="name">名称：<span>毕业实习（机本）</span></div>
                <div class="tuip">主讲：<span>何千于</span><a href="javascript:;" class="into">进入制作</a></div>
                <div class="tuip">环节数：<span>23节</span><a href="javascript:;" class="see">查看课程</a></div>
            </div>
        </div>
        <div class="item">
            <div class="pic">
                <div class="title">
                    <span>毕业实习（机本）</span>
                </div>
<!--                <div class="avatars">
                    <?= Html::img(['/resources/build_course/images/u113.png'], ['width' => '100%']) ?>
                </div>-->
                <div class="float">
                    <span>9855<i class="fa fa-star"></i></span>
                    <span>868<i class="fa fa-thumbs-up"></i></span>
                </div>
            </div>
            <div class="cont">
                <div class="name">名称：<span>毕业实习（机本）</span></div>
                <div class="tuip">主讲：<span>何千于</span><a href="javascript:;" class="into">进入制作</a></div>
                <div class="tuip">环节数：<span>23节</span><a href="javascript:;" class="see">查看课程</a></div>
            </div>
        </div>
        <div class="item item-right">
            <div class="pic">
                <div class="title">
                    <span>毕业实习（机本）</span>
                </div>
<!--                <div class="avatars">
                    <?= Html::img(['/resources/build_course/images/u113.png'], ['width' => '100%']) ?>
                </div>-->
                <div class="float">
                    <span>9855<i class="fa fa-star"></i></span>
                    <span>868<i class="fa fa-thumbs-up"></i></span>
                </div>
            </div>
            <div class="cont">
                <div class="name">名称：<span>毕业实习（机本）</span></div>
                <div class="tuip">主讲：<span>何千于</span><a href="javascript:;" class="into">进入制作</a></div>
                <div class="tuip">环节数：<span>23节</span><a href="javascript:;" class="see">查看课程</a></div>
            </div>
        </div>
        
    </div>
    
</div>
