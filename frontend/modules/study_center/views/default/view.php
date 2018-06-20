<?php

use common\models\vk\Video;
use common\models\vk\VisitLog;
use common\utils\StringUtil;
use common\widgets\share\ShareAsset;
use frontend\modules\study_center\assets\PalyAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Video */


PalyAssets::register($this);
GrowlAsset::register($this);

$shareAssetsPath = $this->assetManager->getPublishedUrl(ShareAsset::register($this)->sourcePath);
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
        <video id="myVideo" src="<?= StringUtil::completeFilePath($model['path']) ?>" controls autoplay poster="<?= StringUtil::completeFilePath($model['img']) ?>" width="100%" height="500"></video>
        <?= $this->render('_node', ['nodes' => $nodes, 'params' => $params]) ?>
    </div>
    <div class="operation">
        <div class="keep-left">
            <?= Html::a('<i class="fa fa-eye"></i>'. $model['play_num']) ?>
            <?= Html::a('<i class="fa ' . ($model['is_favorite'] ? 'fa-heart' : 'fa-heart-o') . '"></i><span>' . ($model['is_favorite'] ? '已收藏' : '收藏') . '</span>', null, [
                'id' => 'favorite', 'class' => 'pointer', 'onclick' => 'favoriteV()'
            ]) ?>
            <?= Html::a('<i class="fa fa-share-alt"></i>分享', null, ['class' => 'pointer', 'onclick' => 'shareShow()']) ?>
            <!-- 分享面板 -->
            <div class="share-panel">
                <div class="panel-body">
                    <div class="qrcode-box">
                        <img id="wx-icon" src="<?= $shareAssetsPath ?>/imgs/wx-logo.png" style="display:none;"/>
                        <canvas class="wx-qrcode"></canvas>
                    </div>
                    <div class="bdsharebuttonbox share-icon-box">
                        <a href="#" class="icon icon-qq" data-cmd="sqq" title="分享到QQ好友"></a>
                        <a href="#" class="icon icon-qzone" data-cmd="qzone" title="分享到QQ空间"></a>
                        <a href="#" class="icon icon-xl" data-cmd="tsina" title="分享到新浪微博"></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="keep-right">
            <?= Html::a('<i class="fa fa-envelope-o"></i>反馈', ['/other/default/feedback']) ?>
            <?= Html::a('<i class="fa fa-arrow-circle-o-right"></i>下一节', 'javascript:;', ['id' => 'next-section']) ?>
            <?= Html::checkbox('autoplay', ArrayHelper::getValue($params, 'checked') ? true : false); ?>自动播放下一节
        </div>
    </div>
    <div class="left-box">
        <div class="panel">
            <div class="panel-head">本节目标</div>
            <div class="panel-body" style="min-height: 500px;">
                <?= str_replace(array("\r\n", "\r", "\n"), "<br/>", $model['des']) ?>
            </div>
        </div>
    </div>
    <div class="right-box">
        <div class="panel">
            <div class="panel-head">主讲老师</div>
            <div class="panel-body">
                <div class="info">
                    <?= Html::beginTag('a', ['href' => Url::to(['/teacher/default/view', 'id' => $model['teacher_id']]), 'target' => '_blank']) ?>
                        <?= Html::img(StringUtil::completeFilePath($model['avatar']), ['class' => 'img-circle', 'width' => 120, 'height' => 120]) ?>
                        <p class="name"><?= $model['teacher_name'] ?></p>
                        <p class="job-title"><?= $model['teacher_des'] ?></p>
                    <?= Html::endTag('a') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$currentId = ArrayHelper::getValue($params, 'id');  //当前页面的id
$knowledgeIds = [];
foreach ($nodes as $node) {
    foreach ($node['knowledges'] as $index => $knowledge) {
        $knowledgeIds[] = $knowledge['knowledge_id'];
    }
}
$knowledgeIds  = json_encode($knowledgeIds );
$data = !empty($model['data']) && !$model['is_finish'] ? (float)$model['data'] : 0; 
$js = 
<<<JS
    //初始二维码分享
    initShare();    
        
    var currentId = "$currentId";   //当前页面的id
    var knowledgeIds = $knowledgeIds;
    var id = getNextToId(currentId, knowledgeIds);
    $("#next-section").attr("href", "../default/view?id=" + id);
   
    /*
     * 媒体操作事件
     */ 
    var myVideo = document.getElementById('myVideo');
    var timeOut;
    myVideo.currentTime = $data;
    //定时执行保存媒体播放进度
    window.saveProgress = function(){
        timeOut = setTimeout(function () {
            $.post("../api/playing",{
                'course_id': "{$model['course_id']}",
                'knowledge_id': "{$model['knowledge_id']}", 
                'percent': myVideo.currentTime.toFixed(0) / {$model['duration']}, 
                'data': myVideo.currentTime.toFixed(0),
            })
            saveProgress();
        }, 2000);
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
        $.post("../api/playend",{
            'course_id': "{$model['course_id']}",
            'knowledge_id': "{$model['knowledge_id']}", 
            'data': myVideo.currentTime.toFixed(0),
        }, function(){
            if($('input[name="autoplay"]').is(":checked")){
                window.location.replace("../default/view?id=" + id + '&checked=1');
            }else{
                myVideo.load();
            }
        })
    }
    //如果当前的播放进度大于0则提示
    if(myVideo.currentTime > 0){
        $.notify({
            message: '已为你切换到上次离开的时间点开始播放！'
        },{
            type: 'success',
            animate: {
                enter: 'animated fadeInRight',
                exit: 'animated fadeOutRight'
            }
        });
    }    
    /**
     * 收藏操作
     * @returns void
     */
    window.favoriteV =  function(){
        if($("#favorite span").html() == '已收藏'){
            //移除收藏
            $.get('../api/del-favorite',{video_id: "{$model['video_id']}"}, function(result){
                if(result.code == 200){
                    //成功
                    $("#favorite span").html('收藏');
                    $("#favorite i").removeClass('fa-heart').addClass('fa-heart-o');
                }
            });
        }else{
            //添加收藏
            $.get('../api/add-favorite',{video_id: "{$model['video_id']}"}, function(result){
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
        
    /*
     * 显示隐藏分享面板
     * @returns {void}     
     **/
    window.shareShow = function(){
        $('.share-panel').finish();
        $('.share-panel').fadeIn();
        $('body').one("mousedown", function(){
            $('.share-panel').finish();
            $('.share-panel').fadeOut();
        });
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>

<!-- 分享代码 -->
<script type="text/javascript">
    window._bd_share_config = {
        "common": {
            "bdSnsKey": {},
            "bdPopTitle":"<?= $model['name'] ?>",
            "bdText": "<?= '该分享来自[游学吧]中国领先的教育网站' ?>",
            "bdMini": "2",
            "bdMiniList": ["qzone", "tsina", "weixin", "renren", "tqq", "tqf", "tieba", "douban", "sqq", "isohu", "ty"],
            "bdPic": "<?= Url::to($model['img'], true) ?>",
            "bdStyle": "1",
            "bdSize": "32"
        },
        "share": {}
    };
    with(document) 0[(getElementsByTagName('head')[0] || body).appendChild(createElement('script')).src = 'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion=' + ~ ( - new Date() / 36e5)];
    
    /**
     * 初始二维码分享
     * @returns {void}
     */
    function initShare(){
        //添加分享图片到第一位置，以便在微信分享时可以被微信捕捉到作为分享缩略图
        $('body').prepend('<div style="overflow:hidden; width:0px; height:0; margin:0 auto; position:absolute; top:0px;"><img src="/<?= $model['img'] ?>"></div>');
        //设置二维码容器大小
        $('.share-panel .wx-qrcode').attr({width:150,height:150});
        //初始微信二维码
        $('.share-panel .wx-qrcode').qrcode({
            // render method: 'canvas', 'image' or 'div'
            render: 'canvas',

            // version range somewhere in 1 .. 40
            minVersion: 1,
            maxVersion: 40,

            // error correction level: 'L', 'M', 'Q' or 'H'
            ecLevel: 'M',

            // size in pixel
            size: 150,

            // code color or image element
            fill: '#000',

            // background color or image element, null for transparent background
            background: null,

            // content
            text: "<?= Url::to([
                '/site/visit','item_id' => $model['id'] , 
                'income' => 'weixin' ,
                'share_by' => Yii::$app->user->id , 
                'item_type' => VisitLog::TYPE_COURSE], true) ?>",

            // corner radius relative to module width: 0.0 .. 0.5
            radius: 0,

            // quiet zone in modules
            quiet: 0,

            // modes
            // 0: normal
            // 1: label strip
            // 2: label box
            // 3: image strip
            // 4: image box
            mode: 4,

            mSize: 0.145,
            mPosX: 0.5,
            mPosY: 0.5,

            label: '',
            fontname: 'sans',
            fontcolor: '#fff',

            image: $('#wx-icon')[0]
        });
    }
    
</script>
