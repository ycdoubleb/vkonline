<?php

use common\models\vk\Video;
use frontend\modules\study_center\assets\PalyAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Video */


PalyAssets::register($this);
GrowlAsset::register($this);

$this->title = $model['name'];
?>

<div class="study_center-default-play main">
    <div class="title">
        <?= Html::a('<i class="fa fa-arrow-circle-o-left"></i>' . Yii::t('app', 'Back'), ['/course/default/view', 'id' => $model['course_id']], ['class' => 'keep-left']) ?>
        <span class="title-name"><?= $model['course_name'] . '&nbsp;&nbsp;' . $model['node_name'] . ' - ' . $model['name'] ?></span>
        <?= Html::a('<i class="fa fa-list-ul"></i>播放列表', 'javascript:;', [
            'id' => 'bars', 'class' => 'keep-right', 'onclick' => '$(".node-list").toggle()'
        ]) ?>
    </div>
    <div class="player">
        <video id="myVideo" src="/<?= $model['path'] ?>" controls autoplay poster="/<?= $model['img'] ?>" width="100%" height="500"></video>
        <?= $this->render('_node', ['nodes' => $nodes, 'params' => $params]) ?>
    </div>
    <div class="operation">
        <div class="keep-left">
            <?= Html::a('<i class="fa fa-eye"></i>'. $model['play_num']) ?>
            <?= Html::a('<i class="fa ' . ($model['is_favorite'] ? 'fa-heart' : 'fa-heart-o') . '"></i><span>' . ($model['is_favorite'] ? '已收藏' : '收藏') . '</span>', 'javascript:;', [
                'id' => 'favorite', 'onclick' => 'favoriteV()'
            ]) ?>
            <?= Html::a('<i class="fa fa-share-alt"></i>', 'javascript:;', ['onclick' => '$(".share-panel").toggle()']) ?>
            <div class="share-panel">
                <div class="title">分享给朋友</div>
                <ul>
                    <li>
                        <div class="content-box">
                            <?= Html::img(['/imgs/course/images/ewm.png'], ['class' => 'code']) ?>
                        </div>
                        <p>扫码分享</p>
                    </li>
                    <li>
                        <div class="content-box">
                            <span class="icon icon-wx"></span>
                            <span class="icon icon-qq"></span>
                            <span class="icon icon-xl"></span>
                            <span class="icon icon-link"></span>
                        </div>
                        <p>扫码分享</p>
                    </li>
                </ul>
            </div>
        </div>
        <div class="keep-right">
            <?= Html::a('<i class="fa fa-envelope-o"></i>反馈', 'javascript:;') ?>
            <?= Html::a('<i class="fa fa-arrow-circle-o-right"></i>下一节', 'javascript:;', ['id' => 'next-section']) ?>
            <?= Html::checkbox('autoplay', ArrayHelper::getValue($params, 'checked') ? true : false); ?>自动播放下一节
        </div>
    </div>
    <div class="left-box">
        <div class="panel">
            <div class="panel-head">本节目标</div>
            <div class="panel-body" style="min-height: 500px;"><?= $model['des'] ?></div>
        </div>
    </div>
    <div class="right-box">
        <div class="panel">
            <div class="panel-head">主讲老师</div>
            <div class="panel-body">
                <div class="info">
                    <?= Html::img([$model['avatar']], ['class' => 'img-circle', 'width' => 120, 'height' => 120]) ?>
                    <p class="name"><?= $model['teacher_name'] ?></p>
                    <p class="job-title"><?= $model['teacher_des'] ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$currentId = ArrayHelper::getValue($params, 'id');
$videoIs = [];
foreach ($nodes as $node) {
    foreach ($node['videos'] as $index => $video) {
        $videoIs[] = $video['video_id'];
    }
}

$videoIs = json_encode($videoIs);
$currentTime = $model['is_finish'] ? $model['finish_time'] : $model['last_time'];
$js = 
<<<JS
    var currentId = "$currentId";
    var videoIds = $videoIs;
    var id = getNextToId(currentId, videoIds);
    $("#next-section").attr("href", "../default/view?id=" + id);
    /*
     * 媒体操作事件
     */
    var myVideo = document.getElementById('myVideo');
    var timeOut;
    myVideo.currentTime = $currentTime
    //定时执行保存媒体播放进度
    window.saveProgress = function(){
        timeOut = setTimeout(function () {
            $.post("../api/playing",{'course_id': "{$model['course_id']}",
                'video_id': "{$model['id']}", 'current_time': myVideo.currentTime.toFixed(0),
            })
            saveProgress();
        }, 30000);
    }
    //媒体播放中执行
    myVideo.onplaying = function(){
        saveProgress();
    };
    //媒体暂停播放执行
    myVideo.onpause = function(){
        clearTimeout(timeOut);
    }
    //媒体播放结束时执行
    myVideo.onended = function(){
        clearTimeout(timeOut);
        $.post("../api/playend",{'course_id': "{$model['course_id']}",
            'video_id': "{$model['id']}",'current_time': myVideo.currentTime.toFixed(0),
        }, function(){
            if($('input[name="autoplay"]').is(":checked")){
                window.location.replace("../default/view?id=" + id + '&checked=1');
            } 
        })
    }   
        
    /**
     * 收藏操作
     * @returns void
     */
    window.favoriteV =  function(){
        if($("#favorite span").html() == '已收藏'){
            //移除收藏
            $.get('../api/del-favorite',{course_id: "{$model['course_id']}", video_id: "{$model['id']}"}, function(result){
                if(result.code == 200){
                    //成功
                    $("#favorite span").html('收藏');
                    $("#favorite i").removeClass('fa-heart').addClass('fa-heart-o');
                }
            });
        }else{
            //添加收藏
            $.get('../api/del-favorite',{course_id: "{$model['course_id']}", video_id: "{$model['id']}"}, function(result){
                if(result.code == 200){
                    //成功
                    $("#favorite span").html('已收藏');
                    $("#favorite i").removeClass('fa-heart-o').addClass('fa-heart');
                    $.notify({
                        message: '收藏成功！请到学习中心查看！'
                    },{
                        type: 'success',
                        animate: {
                            enter: 'animated fadeInRight',
                            exit: 'animated fadeOutRight'
                        }
                    });
                }
            });
        }
    }
            
    /*
     * 获取下一节的视频id
     * @return nextId   //video_id
     */
    function getNextToId(id, data){
        var nextId = "";
        if(data[data.length - 1] == id){
            nextId = data[0];
        }else{
            for(var i in data){
                if(data[i] == id){
                    nextId = data[parseInt(i) + 1];
                    break;
                }
            }
        }    
        return nextId;
    }        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
