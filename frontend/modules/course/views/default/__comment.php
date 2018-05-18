<?php
/* 
 * 课程评论视图 
 */

use common\models\vk\CourseMessage;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
$is_comment = !empty($myComment);
?>
<div class="c-comment">
    <div class="panel">
        <?php if(!$is_comment): ?>
        <!-- 创建评论 -->
        <div class="create-comment-box">
            <form id="user-comment">
                <div class="panel-head scroe">
                    <span>给课程评分：</span>
                    <span class="user-star"></span>
                </div>
                <div class="panel-body">
                    <textarea name="CourseComment[content]" rows="6" class="editer"></textarea>
                    <p class="clearfix"><span class="input_num">0/150</span></p>
                    <a class="btn btn-highlight submit" onclick="submitComment()">提交评论</a>
                </div>
                <?php
                    echo Html::hiddenInput('CourseComment[course_id]', $course_id);
                    echo Html::hiddenInput('CourseComment[user_id]', Yii::$app->user->id);
                ?>
            </form>
        </div>
        <?php else: ?>
        <!-- 你的评论 -->
        <div class="my-comment-box">
            <div class="panel-head">
                <span>你的评论：</span>
            </div>
            <div class="panel-body">
                <div class="comment-item">
                    <div class="avatar-box">
                        <img class="avatar" src="<?= $myComment['user_avatar'] ?>"/>
                        <p class="nickname"><?= $myComment['user_nickname'] ?></p>
                    </div>
                    <div class="comment-body">
                        <span class="scroe" data-score="1"></span>
                        <span class="created_time"><?= Yii::$app->formatter->asRelativeTime($myComment['created_at']) ?></span>
                        <p class="comment-content"><?= $myComment['content'] ?></p>
                        <span class="zan_count"><i class="glyphicon glyphicon-thumbs-up"></i> <?= $myComment['star'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- 其它人评论 -->
        <div class="orther-comment">
            <div class="panel-head">
                学员评论（<?= $max_count ?>）
            </div>
            <div class="panel-body">
                <div id="comments-box"></div>
                <div class="loading-box">
                    <span class="loading"></span>
                    <span class="no_more">没有更多了</span>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    /* 评论DOM */
    var comment_itme_dom = '<div class="comment-item">'
                    +'<div class="avatar-box">'
                        +'<img class="avatar" src="{%user_avatar%}"/>'
                        +'<p class="nickname">{%user_nickname%}</p>'
                    +'</div>'
                    +'<div class="comment-body">'
                       +' <span class="scroe" data-score="{%star%}"></span>'
                        +'<span class="created_time">{%created_at%}</span>'
                        +'<p class="comment-content">{%content%}</p>'
                        +'<span class="zan_count"><i class="glyphicon glyphicon-thumbs-up {%is_praise%}"></i> {%zan_count%}</span>'
                    +'</div>'
                +'</div>';
        
    //同步执行    
    getComment(1);
    //还没评论，初始评论星星
    if(<?= $is_comment ? 0 : 1 ?>){
        initUserCommentStar();
    }
    
    
    
    /**
     * 提交评论
     * @returns {void}
     */
    function submitComment(){
        $.post('/course/api/add-comment',$('.c-comment #user-comment').serialize(),function(result){
            console.log();
        });
    }
    
    /**
     * 获取评论
     * @returns {void}
     */
    function getComment(page){
        var parems = {
            course_id:'<?= $course_id ?>',
            page:'<?= $page ?>'
        };
        $.get('/course/api/get-comment',parems,function(result){
            if(result.code == '200'){
                $.each(result.data.comments,function(){
                    var item = Wskeee.StringUtil.renderDOM(comment_itme_dom,this);
                    console.log(item);
                    $("#comments-box").append(item);
                });
                //初始其它人评论星星
                initOtherUserCommentStar();
            }
        });
    }
    /**
     * 初始用户评分星星 
     **/
    function initUserCommentStar(){
        /* 初始用户评分 */
        $('.c-comment .user-star').raty({
            scoreName: 'CourseComment[star]',
            path     : '/imgs/course/images/raty/',
            size     : 24,
            starHalf : 'star-half-big.png',
            starOff  : 'star-off-big.png',
            starOn   : 'star-on-big.png'
        });
    }
    
    /* 初始其它评论星星 */
    function initOtherUserCommentStar(){
        /* 初始其它用户的评分 */
        $('.c-comment .comment-body .scroe').raty({
            score: function() {
                return $(this).attr('data-score');
            },
            path : '/imgs/course/images/raty/',
            width : false,
            readOnly: true, 
        });
    }
</script>