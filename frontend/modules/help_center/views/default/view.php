<?php

use frontend\modules\help_center\assets\HelpCenterAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$this->title = Yii::t('app', '{Help}{Center}', [
            'Help' => Yii::t('app', 'Help'),
            'Center' => Yii::t('app', 'Center'),
        ]);
?>
<div class="post-view default-view">
    <!--文章显示头部-->
    <div class="post-header">
        <div class="post-title">
            <h4><?= $model['title']?></h4>
        </div>
        <div class="post-caozuo">
            <div class="caozuo">
                <!--文章查看量-->
                <span class="play-volume">
                    <i class="fa fa-eye"></i>
                    <font class="font">
                        <?= $model['view_count']+1 <= 99999 ? number_format($model['view_count']+1) : substr(number_format(((($model['view_count']+1) / 10000) * 10) / 10, 4), 0, -3) . '万'; ?>
                    </font>
                </span>
                <!--点赞部分-->
                <span class="thumbs-up">
                    <a id="thumbs-up" class="btn <?= $isUnlike ? 'disabled': '' ?>" href="#" data-add="<?= $isLike ? 'true' : 'false'?>">
                        <i class="fa <?= $isLike ? 'fa-thumbs-up' : 'fa-thumbs-o-up'?>"></i>
                        <?php $form = ActiveForm::begin([
                            'id' => 'thumbs-up-form'
                        ]); ?>
                        <?= Html::hiddenInput('PostAppraise[post_id]', $model['id']) ?>
                        <?= Html::hiddenInput('PostAppraise[user_id]', Yii::$app->user->id) ?>
                        <?= Html::hiddenInput('PostAppraise[result]', 1) ?>
                        <?php ActiveForm::end(); ?>
                    </a>
                    <font class="font">
                        <?= $model['like_count'] <= 99999 ? number_format($model['like_count']) : substr(number_format((($model['like_count'] / 10000) * 10) / 10, 4), 0, -3) . '万'; ?>
                    </font>
                </span>
                <!--踩部分-->
                <span class="thumbs-down">
                    <a id="thumbs-down" class="btn <?= $isLike ? 'disabled': '' ?>" href="#" data-add="<?= $isUnlike ? 'true' : 'false'?>">
                        <i class="fa <?= $isUnlike ? 'fa-thumbs-down' : 'fa-thumbs-o-down'?>"></i>
                        <?php $form = ActiveForm::begin([
                            'id' => 'thumbs-down-form'
                        ]); ?>
                        <?= Html::hiddenInput('PostAppraise[post_id]', $model['id']) ?>
                        <?= Html::hiddenInput('PostAppraise[user_id]', Yii::$app->user->id) ?>
                        <?= Html::hiddenInput('PostAppraise[result]', 2) ?>
                        <?php ActiveForm::end(); ?>
                    </a>
                    <font class="font">
                        <?= $model['unlike_count'] <= 99999 ? number_format($model['unlike_count']) : substr(number_format((($model['unlike_count'] / 10000) * 10) / 10, 4), 0, -3) . '万'; ?>
                    </font>
                </span>
            </div>
        </div>
    </div>
    <!--文章显示区域-->
    <div class="post-content">
        <div class="post-title">
            <?= $model['title'] ?>
        </div>
        <?php
            $content = Html::decode($model['content']);
            echo $content;
        ?>
    </div>
    <!--显示最后修改时间-->
    <div class="updated_at">
        <div class="time"><span>最后修改：</span>
            <?= date('Y-m-d', $model['updated_at']);?>
        </div>
    </div>
    <!--显示上一篇or下一篇按钮-->
    <div class="page">
        <span class="page-left">上一篇： 
            <a href="<?=$page['prev_article']['url']?>">
                <?=$page['prev_article']['title']?>
            </a>
        </span>
        <span class="page-right">下一篇：
            <a href="<?=$page['next_article']['url']?>">
                <?=$page['next_article']['title']?>
            </a>
        </span>
    </div>
    <?php
        //能否评论（0不可以，1可以）
        if($model['can_comment'] == 1):        
    ?>
    <!--评论框框-->
    <div class="frame" style="width: 100%">
        <div class="col-xs-12 frame-title">
            <i class="icon fa fa-commenting"></i>
            <span><?= Yii::t('app', 'Comment')?>（<span id="number"><?= $number ?></span>）</span>
        </div>
        <div class="col-xs-12 frame-table message" style="margin-bottom: 0px">
            <div id="mes-list" class="meslist">

            </div>
            <div class="mesform">
                <div class="col-xs-11 mesinput">

                    <?php $form = ActiveForm::begin([
                        'options'=>[
                            'id' => 'form-message',
                            'class'=>'form-horizontal',
                        ],
                        'action'=>['create-message', 'post_id'=>$model['id']]
                    ]); ?>

                    <?= Html::textarea('content',null,['placeholder'=>'点击输入评论...']);  ?>

                    <?php ActiveForm::end(); ?>

                </div>
                <div class="col-xs-1 mesbtn">
                    <?= Html::a(Yii::t('app', 'Comment'), 'javascript:;', ['id'=>'submitsave', 'class'=>'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php 
$meslist = Url::to(['mes-index','post_id'=>$model['id']]);
$createmes = Url::to(['create-message', 'post_id'=>$model['id']]);
$js = 
<<<JS
        
    //加载留言列表
    $("#mes-list").load("$meslist"); 
    //提交表单
    $("#submitsave").click(function(){
        var number = $("#number").text();
        $.post("$createmes",$('#form-message').serialize(),function(data){
            if(data['code'] == '200'){
                $("#number").text(parseInt(number) + parseInt(data['num']));
                $("#mes-list").load("$meslist"); 
                $("#form-message textarea").val("");
            }
        });
    });
JS;
    $this->registerJs($js,  View::POS_READY);
?>
<?php
    HelpCenterAssets::register($this);
?>