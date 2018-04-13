<?php

use common\models\vk\CourseMessage;
use common\models\vk\Video;
use frontend\modules\study_center\assets\MainAssets;
use frontend\modules\study_center\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Video */


MainAssets::register($this);
ModuleAssets::register($this);

?>

<header class="header filling">
    <div class="course">
        <h3><?= $model->courseNode->course->name ?></h3>
        <p>本课共有 <span><?= $videoNum['node_num'] ?>&nbsp;个环节</span></p>
    </div>
    <div class="share">
        <div class="share-btns"></div>
    </div>
</header>

<div class="content center">
    <div class="study_center-default-play main">
        
        <div class="video">
            <div class="title">
                <span><i class="fa fa-video-camera"></i><?= $model->name ?></span>
                <?= Html::a('<i class="fa fa-bars"></i>', 'javascript:;', ['id' => 'bars', 'class' => 'right']) ?>
            </div>
            <div class="player">
                <!--<i class="fa fa-pause-circle-o"></i>-->
                <?= Html::a('<i class="fa fa-play-circle-o"></i>', 'javascript:;') ?>
                <video id="myVideo" src="/<?= $model->source->path ?>" width="100%" height="500"></video>
            </div>
            <div class="catalog">
                <ul class="sortable list">
                    <?php foreach($courseNodes as $index => $nodes): ?>
                    <li id="<?= $nodes->id ?>">
                        <div class="head">
                            <?= Html::a("<i class=\"fa fa-minus-square\"></i><span class=\"name\">{$nodes->name}</span>", "#toggle_{$nodes->id}", ['data-toggle'=>'collapse', 'aria-expanded'=> 'true', 'onclick'=>'replace($(this))']); ?>
                        </div>
                        <div id="toggle_<?= $nodes->id ?>" class="collapse in foot" aria-expanded="true">
                            <ul class="sortable list">
                                <?php foreach($nodes->videos as $video): ?>
                                <li>
                                    <div class="head nodes <?= $model->id == $video->id ? 'active' :  null ?>">
                                        <?php 
                                            if($model->id == $video->id){
                                                echo Html::a("<span class=\"name\">{$video->name}</span><i class=\"fa fa-play-circle\" style=\"display: block;\"></i>", ['view', 'id' => $video->id]);
                                            }else{
                                                echo Html::a("<span class=\"name\">{$video->name}</span><i class=\"fa fa-play-circle\"></i>", ['view', 'id' => $video->id]);
                                            }
                                        ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="iframe">
            <?= Html::a("<span class=\"watch\">{$playNum['play_num']}<i class=\"fa fa-eye\"></i></span>"); ?>
            <?php
                if($praise->isNewRecord){
                    echo Html::a("<span class=\"zan\">{$model->zan_count}<i class=\"fa fa-thumbs-o-up\"></i></span>", ['praise', 'id' => $model->id], ['id' => 'praise', 'data-toggled' => 'false']);
                }else{
                    echo Html::a("<span class=\"zan\">{$model->zan_count}<i class=\"fa fa-thumbs-up\"></i></span>", ['praise', 'id' => $model->id], ['id' => 'praise', 'data-toggled' => 'true']);
                }
                if($collect->isNewRecord){
                    echo Html::a("<span class=\"fave right\"><i class=\"fa fa-heart-o\"></i>收藏<span class=\"favenum\">（{$model->favorite_count}）</span></span>", ['collect', 'id' => $model->id], ['id' => 'collect', 'data-toggled' => 'false']);
                }else{
                    echo Html::a("<span class=\"fave right\"><i class=\"fa fa-heart\"></i>已收藏<span class=\"favenum\">（{$model->favorite_count}）</span></span>", ['collect', 'id' => $model->id], ['id' => 'collect', 'data-toggled' => 'true']);
                }
            ?>
        </div>
        
        <div class="tabs">
            <ul role="tablist">
                <li role="presentation" class="active">
                    <?= Html::a(Yii::t('app', '{Lessons}{Target}', [
                        'Lessons' => Yii::t('app', 'Lessons'), 'Target' => Yii::t('app', 'Target')
                    ]), '#target', ['role' => 'tab', 'data-toggle' => 'tab', 'aria-controls' => 'catalog', 'aria-expanded' => true]) ?>
                </li>
                <li role="presentation">
                    <?= Html::a(Yii::t('app', 'Message'), '#msg', ['role' => 'tab', 'data-toggle' => 'tab', 'aria-controls' => 'msg']) ?>
                </li>
            </ul>
        </div>
        <div class="tab-content">
            <div id="target" class="tab-pane fade active in"  role="tabpanel" aria-labelledby="target-tab">
                <div class="target"><?= $model->des ?></div>
            </div>
            <div id="msg" class="tab-pane fade" role="tabpanel" aria-labelledby="msg-tab">
                <div class="col-xs-12 frame">
                    <div class="col-xs-12 table">
                        <div id="msg_list" class="msglist">
                            <?= $this->render('message', ['dataProvider' => $msgDataProvider]) ?>
                        </div>
                        <div class="msgform">
                            <div class="col-xs-11 msginput">

                                <?php $form = ActiveForm::begin([
                                    'options'=>['id' => 'msg-form', 'class'=>'form-horizontal','method' => 'post'],
                                    'action'=>['add-msg', 'course_id' => $model->courseNode->course_id, 'video_id' => $model->id]
                                ]); ?>

                                <?= Html::textarea('content', null, ['placeholder' => '请输入你想说的话...']);  ?>

                                <?php ActiveForm::end(); ?>

                            </div>
                            <div class="col-xs-1 msgbtn">
                                <?= Html::a(Yii::t('app', 'Message'), 'javascript:;', ['id'=>'submitsave', 'class'=>'btn btn-primary']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="sidebars">
            <h2><?= Yii::t('app', '{MainSpeak}{Teacher}', ['MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')])?></h2>
            <div class="teacher">
                <?= Html::img([$model->teacher->avatar], ['class' => 'img-circle', 'width' => 96, 'height' => 96]) ?>
                <p><span><?= Html::encode($model->teacher->name) ?></span></p>
                <div class="des"><?= Html::encode($model->teacher->des) ?></div>
            </div>
        </div>
        
    </div>
</div>

<?php

$msgType = CourseMessage::VIDEO_TYPE;
if(!empty($model->progress)){
    if($model->progress->is_finish){
        $currentTime = $model->progress->finish_time;
    }else{
        $currentTime = $model->progress->last_time;
    }
}else{
    $currentTime = 0;
}
$js = 
<<<JS
    var myVideo = document.getElementById('myVideo');
    var timeOut;
    myVideo.currentTime = $currentTime;
    //定时执行保存媒体播放进度
    window.saveProgress = function(){
        timeOut = setTimeout(function () {
            $.post("../default/playing",{'course_id': "{$model->courseNode->course_id}",
                'video_id': "{$model->id}",'current_time': myVideo.currentTime.toFixed(0),
            })
            saveProgress();
        }, 30000);
    }
    //单击媒体时执行
    myVideo.onclick = function(){
        if(myVideo.paused){
            myVideo.play();
            //myVideo.previousElementSibling.innerHTML = '<i class="fa fa-play-circle-o"></i>';
            myVideo.previousElementSibling.style.cssText = "display: none";
            saveProgress();
        }else{
            myVideo.pause();
            clearTimeout(timeOut);
            myVideo.previousElementSibling.innerHTML = '<i class="fa fa-pause-circle-o"></i>';
            myVideo.previousElementSibling.style.cssText = "display: block";
        }
    };
    //媒体播放结束时执行
    myVideo.onended = function(){
        clearTimeout(timeOut);
        myVideo.previousElementSibling.innerHTML = '<i class="fa fa-play-circle-o"></i>';
        myVideo.previousElementSibling.style.cssText = "display: block";
        $.post("../default/playend",{'course_id': "{$model->courseNode->course_id}",
            'video_id': "{$model->id}",'current_time': myVideo.currentTime.toFixed(0),
        })
    }
    //显示和隐藏目录 
    $("#bars").click(function(){
        if($(".catalog").css("right") == "0px"){
            $(".catalog").animate({right: "-255px"}, 500, 'linear');
            $(".player").animate({width: "100%"}, 500, 'linear');
            //填充父级div的高宽大小，也就是铺满整个div
            $("#myVideo").css({"object-fit": "fill"});
        }else{
            $(".player").animate({width: "945px"}, 500, 'linear', function(){
                $("#myVideo").css({"object-fit": "contain"});
            });
            $(".catalog").animate({right: 0}, 500, 'linear');
        }
    });    
    //替换图标
    window.replace = function (elem){
        if(elem.attr('aria-expanded') == 'true'){
            elem.children('i').removeClass('fa-minus-square').addClass('fa-plus-square');
        }else{
            elem.children('i').removeClass('fa-plus-square').addClass('fa-minus-square');
        }
    }
    //点击收藏
    $('#collect').click(function(e){
        e.preventDefault();
        var elem = $(this);
        $.get($(this).attr('href'), function(rel){
            if(rel['code'] == '200'){
                if(elem.attr('data-toggled') == 'false'){
                    elem.find('span.fave').html('<i class="fa fa-heart"></i>已收藏<span class="favenum">（'+ rel['data'] +'）</span>');
                    elem.attr('data-toggled', true);
                }else{
                    elem.find('span.fave').html('<i class="fa fa-heart-o"></i>收藏<span class="favenum">（'+ rel['data'] +'）</span>');
                    elem.attr('data-toggled', false);
                }
            }else{
                alert(rel['message'])
            }
        });
    });
    //点击点赞
    $('#praise').click(function(e){
        e.preventDefault();
        var elem = $(this);
        $.get($(this).attr('href'), function(rel){
            if(rel['code'] == '200'){
                if(elem.attr('data-toggled') == 'false'){
                    elem.find('span.zan').html(rel['data'] + '<i class="fa fa-thumbs-up"></i>');
                    elem.attr('data-toggled', true);
                }else{
                    elem.find('span.zan').html(rel['data'] + '<i class="fa fa-thumbs-o-up"></i>');
                    elem.attr('data-toggled', false);
                }
            }else{
                alert(rel['message'])
            }
        });
    });
    //提交表单
    $('#submitsave').click(function(){
        //$('#msg-form').submit();return;
        $.post("../default/add-msg?course_id={$model->courseNode->course_id}&video_id={$model->id}", $('#msg-form').serialize(), function(rel){
            if(rel['code'] == '200'){
                $('#msg_list').load("../default/msg-index?video_id={$model->id}&type={$msgType}"); 
                $('#msg-form textarea').val('');
            }
        });
    });
JS;
    $this->registerJs($js,  View::POS_READY);
?>