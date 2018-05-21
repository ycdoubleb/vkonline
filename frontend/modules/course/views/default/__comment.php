<?php
/* 
 * 课程评论视图 
 */

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
                        <span class="scroe" data-score="<?= $myComment['star'] ?>"></span>
                        <span class="created_time"><?= Yii::$app->formatter->asRelativeTime($myComment['created_at']) ?></span>
                        <p class="comment-content"><?= $myComment['content'] ?></p>
                        <span class="zan_count"><i class="glyphicon glyphicon-thumbs-up"></i> <?= $myComment['zan_count'] ?></span>
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
                    <a href="javascript:" onclick="getComment(currentPage+1)"><p class="get-more">显示更多</p></a>
                    <span class="loading"></span>
                    <span class="no-more">没有更多了</span>
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
                        +'<div class="zan_count {%is_praise%}" data-comment-id="{%comment_id%}">'
                            +'<i class="glyphicon glyphicon-thumbs-up"></i>'
                            +'<sapn class="txt">{%zan_count%}</span>'
                        +'</div>'
                    +'</div>'
                +'</div>';
        
    //同步执行   
    var max_count = <?= $max_count ?>           //所有评论数
    var size = 10;                               //每页显示评论数
    var currentPage = 1                          //当前页
    var totalPage = Math.ceil(max_count/size);   //计算最大页数
    
    getComment(currentPage);
    //还没评论，初始评论星星
    if(<?= $is_comment ? 0 : 1 ?>){
        initUserCommentAct();
    }
    
    
    
    /**
     * 提交评论
     * @returns {void}
     */
    function submitComment(){
        $.post('/course/api/add-comment',$('.c-comment #user-comment').serialize(),function(result){
            //调用父级重新加载评论页
            loadContent({id:'course_comment',url:'/course/default/get-comment'},true);
        });
    }
    
    /**
     * 获取评论
     * @returns {void}
     */
    function getComment(page){
        var parems = {
            course_id:'<?= $course_id ?>',
            page:page,
            szie:size,
        };
        currentPage = page;
        $('.loading-box .get-more').hide();
        $('.loading-box .no-more').hide();
        $('.loading-box .loading').show();
        $.get('/course/api/get-comment',parems,function(result){
            if(result.code == '200' && result.data.error == undefined){
                $('.loading-box .loading').hide();
                $.each(result.data.comments,function(){
                    //已经点赞改变样式
                    this['is_praise'] = this['is_praise'] == "1" ? 'is_praise' : "";
                    var item = $(Wskeee.StringUtil.renderDOM(comment_itme_dom,this)).appendTo($("#comments-box"));
                    initZanAct($(item).find('.zan_count'));
                    if(currentPage != totalPage){
                        $('.loading-box .get-more').show();
                    }else{
                        $('.loading-box .no-more').show();
                    }
                });
                //初始其它人评论星星
                initOtherUserCommentAct();
            }
        });
    }
    /**
     * 初始用户评分星星 
     **/
    function initUserCommentAct(){
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
    
    /* 初始其它评论星星和点赞操作 */
    function initOtherUserCommentAct(){
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
    
    /**
     * 初始点赞操作
     * @param {DOM} $item   
     * @returns {undefined}     
     **/
    function initZanAct($item){
        $item.on('click',function(){
            var _this = $(this);
            if(!$(this).hasClass('is_praise')){
               $.post('/course/api/add-comment-praise',{
                   "CommentPraise[comment_id]":$(this).attr('data-comment-id'),
                   "CommentPraise[user_id]":'<?= Yii::$app->user->id ?>',
                   "CommentPraise[result]":1,
                },function(result){
                   if(result.code == 200 && result.data.error == undefined){
                       console.log(_this);
                       _this.addClass('is_praise');
                       _this.find('.txt').html(result.data.zan_count);
                   }
               });
           }
        });
    }
</script>