<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\Video;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\LinkPager;

/* @var $this View */

$this->title = Yii::t('app', "{Add}{Video}",[
    'Add' => Yii::t('app', 'Add'), 'Video' => Yii::t('app', 'Video')
]);

//当前action
$actionId = Yii::$app->controller->action->id;

?>

<div class="knowledge-reference">
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters,
        'type' => $type,
        'actionId' => $actionId,
        'pathMap' => $pathMap,
    ]) ?>
    
    <!-- 显示结果 -->
    <div class="summary">
        <span>搜索结果： 共搜索到 <b><?= $totalCount ?></b> 个视频 </span>
        <i class="fa fa-times-circle times-close" aria-hidden="true"></i>
    </div>
    
    <!--列表-->
    <div class="vk-list">
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table detail-view vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'summaryOptions' => ['class' => 'hidden'],
            'pager' => [
                'options' => [
                    'class' => 'hidden',
                ]
            ],
            'columns' => [
                [
                    'header' => '',
                    'headerOptions' => ['style' => 'width: 20px'],
                    'format' => 'raw',
                    'value' => function($model){
                        return Html::checkbox('Video[id]', false, ['class' => 'hidden', 'value' => $model['id']]);
                    }
                ],
                [
                    'attribute' => 'img',
                    'header' => Yii::t('app', '{Preview}{Image}', [
                        'Preview' => Yii::t('app', 'Preview'), 'Image' => Yii::t('app', 'Image'),
                    ]),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width:137px'],
                    'contentOptions' => ['style' => 'text-align:left; height: 76px'],
                    'format' => 'raw',
                    'value' => function ($model){
                        return Html::img(Aliyun::absolutePath($model['img']), ['width' => 121, 'height' => 68]);
                    },
                ],
                [
                    'attribute' => 'name',
                    'header' => Yii::t('app', '{Video}{Name}', [
                        'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width:200px'],
                    'contentOptions' => ['style' => 'white-space: unset;'],
                ],
                [
                    'attribute' => 'teacher_name',
                    'header' => Yii::t('app', '{MainSpeak}{Teacher}', [
                        'MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ]),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width:80px'],
                ],
                [
                    'attribute' => 'level',
                    'header' => Yii::t('app', '{View}{Privilege}', [
                        'View' => Yii::t('app', 'View'), 'Privilege' => Yii::t('app', 'Privilege')
                    ]),
                    'filter' => false,
                    'value' => function ($model){
                        return Video::$levelMap[$model['level']];
                    },
                    'headerOptions' => ['style' => 'width:80px'],
                ],
                [
                    'attribute' => 'mts_status',
                    'header' => Yii::t('app', 'Mts Status'),
                    'filter' => false,
                    'value' => function ($model){
                        return Video::$mtsStatusName[$model['mts_status']];
                    },
                    'headerOptions' => ['style' => 'width:80px'],
                ],
                [
                    'header' => Yii::t('app', '{The}{Catalog}', [
                        'The' => Yii::t('app', 'The'), 'Catalog' => Yii::t('app', 'Catalog')
                    ]),
                    'format' => 'raw',
                    'filter' => false,
                    'value' => function ($model) use($pathMap){
                        $videoPath = '';
                        if(isset($pathMap[$model['user_cat_id']]) && count($pathMap[$model['user_cat_id']]) > 0){
                            $endPath = end($pathMap[$model['user_cat_id']]);
                            foreach ($pathMap[$model['user_cat_id']] as $path) {
                                if($path['id'] != $endPath['id']){
                                    $videoPath .= $path['name']. '<span class="set-route">›</span>';
                                }else{
                                    $videoPath .= $path['name'];
                                }
                            }
                            return $videoPath;
                        }else{
                            return null;
                        }
                    },
                    'headerOptions' => ['style' => 'width:365px'],
                ],
            ],
        ]); ?>
        
        <!--分页-->
        <?= LinkPager::widget([  
            'pagination' => new Pagination([
                'totalCount' => $totalCount,  
            ]),  
        ]) ?> 
        
    </div>
        
</div>

<?= $this->render('/layouts/model') ?>

<?php
//用户分类id
$userCatId = ArrayHelper::getValue($filters, 'user_cat_id', null);  
$js = 
<<<JS
    
    //删除搜索条件
    $('.times-close').click(function(){
        $("#reference-video-list").load("../knowledge/my-video?user_cat_id={$userCatId}");
    });
        
    //单击分页    
    $('.pagination > li').each(function(){
        $(this).click(function(e){
            e.preventDefault();
            $("#reference-video-list").load($(this).find('a').attr('href'));
        });
    });    
        
    //设置table的每个tr的跳转链接    
    $('.vk-table > tbody > tr').each(function(){
        var url = "../knowledge/choice?video_id=" + $(this).attr('data-key');
        $(this).click(function(){
            window.clickChoiceEvent(url);
        });
    });    
        
    /**
     * 单击选择事件
     * @param string url 链接
     */
    window.clickChoiceEvent = function(url){
        $.get(url, function(rel){
            var data = rel.data.result;
            //请求成功返回数据，否则提示错误信息
            if(rel['code'] == '200'){
                $("#video-details .vk-list > ul").html("");
                $(Wskeee.StringUtil.renderDOM(window.list_dom, data)).appendTo($("#video-details .vk-list > ul"));
                $('#operation').html("重选");
                $('input[name="Resource[res_id]"]').val(data.id);
                $('input[name="Resource[data]"]').val(data.duration);
                $(".field-video-details").removeClass("hidden");
                $("#fill").removeClass("hidden");
                $("#reference-video-list").addClass("hidden").html("");
                $("#knowledge-info").removeClass("hidden");
            }else{
                alert(rel['message']);
            }
        });
        return false;
    }
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>