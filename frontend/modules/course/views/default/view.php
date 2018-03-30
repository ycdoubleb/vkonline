<?php

use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\PraiseLog;
use frontend\modules\course\assets\MainAssets;
use frontend\modules\course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Course */
/* @var $favorite CourseFavorite */
/* @var $praise PraiseLog */


MainAssets::register($this);
ModuleAssets::register($this);

$this->title = Yii::t('app', 'Course');

?>

<style type="text/css">
    body .wrap > .container {
        width: 100%;
    }
</style>

<header class="header filling">
    <div class="content center">
        <div class="course">
            <h2><?= $model->name ?></h2>
            <p>环节数：<span><?= $video['node_num'] ?>&nbsp;节</span></p>
        </div>
        <div class="follow">
            <div class="follow-btns"></div>
        </div>
        <p>
            <?php
                if($favorite->isNewRecord){
                    echo Html::a("<span class=\"star\">{$model->favorite_count}<i class=\"fa fa-star-o\"></i></span>", ['favorite', 'id' => $model->id], ['id' => 'favorite', 'data-toggled' => 'false']);
                }else{
                    echo Html::a("<span class=\"star\">{$model->favorite_count}<i class=\"fa fa-star\"></i></span>", ['favorite', 'id' => $model->id], ['id' => 'favorite', 'data-toggled' => 'true']);
                    echo '<span class="star right"><i class="fa fa-star"></i>已关注</span>';
                }
                if($praise->isNewRecord){
                    echo Html::a("<span class=\"zan\">{$model->zan_count}<i class=\"fa fa-thumbs-o-up\"></i></span>", ['praise', 'id' => $model->id], ['id' => 'praise', 'data-toggled' => 'false']);
                }else{
                    echo Html::a("<span class=\"zan\">{$model->zan_count}<i class=\"fa fa-thumbs-up\"></i></span>", ['praise', 'id' => $model->id], ['id' => 'praise', 'data-toggled' => 'true']);
                }
            ?>
        </p>
    </div>
</header>

<div class="content center">
    
    <div class="course-default-view main">
        <div class="tabs">
            <ul role="tablist">
                <li role="presentation" class="active">
                    <?= Html::a(Yii::t('app', '{Course}{Catalog}', [
                        'Course' => Yii::t('app', 'Course'), 'Catalog' => Yii::t('app', 'Catalog')
                    ]), '#catalog', ['role' => 'tab', 'data-toggle' => 'tab', 'aria-controls' => 'catalog', 'aria-expanded' => true]) ?>
                </li>
                <li role="presentation">
                    <?= Html::a(Yii::t('app', 'Message'), '#msg', ['role' => 'tab', 'data-toggle' => 'tab', 'aria-controls' => 'msg']) ?>
                </li>
            </ul>
        </div>
        <div class="tab-content">
            <div id="catalog" class="tab-pane fade active in"  role="tabpanel" aria-labelledby="catalog-tab">
                <ul class="sortable list">
                    <?php $endNodes = end($dataProvider); ?>
                    <?php foreach($dataProvider as $index => $nodes): ?>
                    <li id="<?= $nodes->id ?>">
                        <div class="head <?= $nodes->id == $endNodes->id ? 'remove' : ''?>">
                            <?php  
                                $videoNum = count($nodes->videos);
                                if($index == 0){
                                    echo Html::a("<i class=\"fa fa-minus-square\"></i><span class=\"name\">{$nodes->name}<span class=\"number\">（{$videoNum}）</span></span>", "#toggle_{$nodes->id}", ['data-toggle'=>'collapse', 'aria-expanded'=> 'true', 'onclick'=>'replace($(this))']);
                                }else{
                                    echo Html::a("<i class=\"fa fa-plus-square\"></i><span class=\"name\">{$nodes->name}<span class=\"number\">（{$videoNum}）</span></span>", "#toggle_{$nodes->id}", ['data-toggle'=>'collapse', 'aria-expanded'=> 'false', 'onclick'=>'replace($(this))']);
                                } 
                            ?>
                        </div>
                        <?php if($index == 0): ?>
                        <div id="toggle_<?= $nodes->id ?>" class="collapse in foot <?= $nodes->id == $endNodes->id ? 'remove' : ''?>" aria-expanded="true">
                        <?php else: ?>
                        <div id="toggle_<?= $nodes->id ?>" class="collapse foot <?= $nodes->id == $endNodes->id ? 'remove' : ''?>" aria-expanded="false">
                        <?php endif; ?>
                            <ul class="sortable list">
                                <?php foreach($nodes->videos as $video): ?>
                                <li>
                                    <div class="head nodes">
                                        <?= Html::a("<span class=\"name\">{$video->name}</span><i class=\"fa fa-play-circle\"></i>", ['/study_center/default/play', 'id' => $model->id]) ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div id="msg" class="tab-pane fade" role="tabpanel" aria-labelledby="msg-tab">
                <div class="col-xs-12 frame">
                    <div class="col-xs-12 table">
                        <div id="msg_list" class="msglist">
                            <?= $this->render('message', ['dataProvider' => $msgProvider]) ?>
                        </div>
                        <div class="msgform">
                            <div class="col-xs-11 msginput">

                                <?php $form = ActiveForm::begin([
                                    'options'=>['id' => 'msg-form', 'class'=>'form-horizontal','method' => 'post',],
                                    'action'=>['add-msg', 'id' => $model->id]
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

$js = 
<<<JS
    
    //替换图标
    window.replace = function (elem){
        if(elem.attr('aria-expanded') == 'true'){
            elem.children('i').removeClass('fa-minus-square').addClass('fa-plus-square');
        }else{
            elem.children('i').removeClass('fa-plus-square').addClass('fa-minus-square');
        }
    }
    //点击关注
    $('#favorite').click(function(e){
        e.preventDefault();
        var elem = $(this);
        $.get($(this).attr('href'), function(rel){
            if(rel['code'] == '200'){
                if(elem.attr('data-toggled') == 'false'){
                    elem.find('span.star').html(rel['data'] + '<i class="fa fa-star"></i>');
                    elem.attr('data-toggled', true);
                    elem.parent('p').append('<span class="star right"><i class="fa fa-star"></i>已关注</span>');
                }else{
                    elem.find('span.star').html(rel['data'] + '<i class="fa fa-star-o"></i>');
                    elem.attr('data-toggled', false);
                    elem.siblings('span').remove();
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
        $.post("../default/add-msg?id={$model->id}", $('#msg-form').serialize(), function(rel){
            if(rel['code'] == '200'){
                $('#msg_list').load("../default/msg-index?course_id={$model->id}"); 
                $('#msg-form textarea').val('');
            }
        });
    });

JS;
    $this->registerJs($js,  View::POS_READY);
?>