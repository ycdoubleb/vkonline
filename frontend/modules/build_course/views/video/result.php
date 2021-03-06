<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\utils\StringUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\LinkPager;

/* @var $this View */

ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t('app', '{My}{Video}', [
    'My' => Yii::t('app', 'My'), 'Video' => Yii::t('app', 'Video')
]);

?>

<div class="video-index vk-material main">
    
    <!--页面标题-->
    <div class="vk-title clear-margin">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?php
                echo Html::a(Yii::t('app', '{Catalog}{Admin}', [
                        'Catalog' => Yii::t('app', 'Catalog'), 'Admin' => Yii::t('app', 'Admin')
                    ]), ['user-category/index'], ['class' => 'btn btn-unimportant btn-flat']);
            ?>
        </div>
    </div>
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters,
        'locationPathMap' => $locationPathMap,
        'teacherMap' => $teacherMap,
    ]) ?>
    
    <!-- 显示结果 -->
    <div class="vk-tabs">
        <ul class="list-unstyled pull-left">
            <li>
                <span class="summary">
                    搜索结果： 共搜索到 <b><?= $totalCount ?></b> 个视频素材
                    <?= Html::a('<i class="fa fa-times-circle" aria-hidden="true"></i>', ['index', 
                        'user_cat_id' => ArrayHelper::getValue($filters, 'user_cat_id'),
                        'type' => ArrayHelper::getValue($filters, 'type')
                    ]) ?>
                    
                </span>
            </li>
        </ul>
        <ul class="list-unstyled pull-right hidden">
            <li>
                <?= Html::a('全选', 'javascript:;', ['id' => 'allChecked', 'style' => 'padding: 0px 10px']) ?>
            </li>
            <li>
                <?= Html::a('全不选', 'javascript:;', ['id' => 'noAllChecked', 'style' => 'padding: 0px 10px']) ?>
            </li>
            <li>
                <span style="padding: 0px 5px; line-height: 54px;">
                    <?= Html::a(Yii::t('app', 'Confirm'), ['arrange/move', 'table_name' => 'video'], [
                        'id' => 'move', 'class' => 'btn btn-primary btn-flat',
                        'onclick' => 'showCatalogModal($(this)); return false;'
                    ]) ?>
                </span>
            </li>
            <li>
                <span style="padding: 0px 5px; line-height: 54px;">
                    <?= Html::a(Yii::t('app', 'Cancel'), 'javascript:;', ['id' => 'cancel', 'class' => 'btn btn-default btn-flat']) ?>
                </span>
            </li>
        </ul>
    </div>
    
    <!--列表-->
    <div class="vk-list set-bottom set-padding">
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered table-hover detail-view vk-table'],
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
                    'attribute' => 'type',
                    'header' => Yii::t('app', '{Material}{Type}', [
                        'Material' => Yii::t('app', 'Material'), 'Type' => Yii::t('app', 'Type')
                    ]),
                    'filter' => false,
                    'value' => function($model){
                        return Video::$typeMap[$model['type']];  
                    },
                    'headerOptions' => ['style' => 'width:40px'],
                ],
                [
                    'attribute' => 'img',
                    'header' => Yii::t('app', '{Preview}{Image}', [
                        'Preview' => Yii::t('app', 'Preview'), 'Image' => Yii::t('app', 'Image'),
                    ]),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width:125px'],
                    'contentOptions' => ['style' => 'text-align:left; height: 76px'],
                    'format' => 'raw',
                    'value' => function ($model){
                        switch ($model['video_type']){
                            case Video::TYPE_VIDEO :
                                $imgPath = Aliyun::absolutePath(!empty($model['img']) ? $model['img'] : 'static/imgs/notfound.png');
                                break;
                            case Video::TYPE_AUDIO :
                                $imgPath = StringUtil::completeFilePath('/imgs/build_course/images/audio.png');
                                break;
                            case Video::TYPE_IMAGE :
                                $imgPath = Aliyun::absolutePath(!empty($model['img']) ? $model['img'] : 'static/imgs/notfound.png');
                                break;
                            case Video::TYPE_DOCUMENT :
                                $imgPath = StringUtil::completeFilePath('/imgs/build_course/images/' . StringUtil::getFileExtensionName(Aliyun::absolutePath($model['oss_key'])) . '.png');
                                break;
                            default :
                                $imgPath = Aliyun::absolutePath('static/imgs/notfound.png');
                                break;
                        }
                    
                        return Html::img($imgPath, ['width' => 121, 'height' => 68]);
                    },
                ],
                [
                    'attribute' => 'name',
                    'header' => Yii::t('app', '{Material}{Name}', [
                        'Material' => Yii::t('app', 'Material'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width:180px'],
                    'contentOptions' => [
                        'class' => 'single-clamp',
                        'style' => 'white-space: unset;'
                    ],
                ],
                [
                    'attribute' => 'teacher_name',
                    'header' => Yii::t('app', '{MainSpeak}{Teacher}', [
                        'MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ]),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width:80px'],
                ],
//                [
//                    'attribute' => 'level',
//                    'header' => Yii::t('app', '{View}{Privilege}', [
//                        'View' => Yii::t('app', 'View'), 'Privilege' => Yii::t('app', 'Privilege')
//                    ]),
//                    'filter' => false,
//                    'value' => function ($model){
//                        return Video::$levelMap[$model['level']];
//                    },
//                    'headerOptions' => ['style' => 'width:80px'],
//                ],
                [
                    'attribute' => 'mts_status',
                    'header' => Yii::t('app', 'Mts Status'),
                    'filter' => false,
                    'value' => function ($model){
                        return $model['type'] == Video::TYPE_VIDEO ? Video::$mtsStatusName[$model['mts_status']] : null;
                    },
                    'headerOptions' => ['style' => 'width:80px'],
                ],
                [
                    'header' => Yii::t('app', '{The}{Catalog}', [
                        'The' => Yii::t('app', 'The'), 'Catalog' => Yii::t('app', 'Catalog')
                    ]),
                    'format' => 'raw',
                    'filter' => false,
                    'value' => function ($model){
                        $pathMap = '';
                        $locationPathMap = UserCategory::getUserCatLocationPath($model['user_cat_id']);
                        if(isset($locationPathMap[$model['user_cat_id']]) && count($locationPathMap[$model['user_cat_id']]) > 0){
                            $endPath = end($locationPathMap[$model['user_cat_id']]);
                            foreach ($locationPathMap[$model['user_cat_id']] as $path) {
                                if($path['id'] != $endPath['id']){
                                    $pathMap .= $path['name']. '<span class="set-route">›</span>';
                                }else{
                                    $pathMap .= $path['name'];
                                }
                            }
                            return $pathMap;
                        }else{
                            return '根目录';
                        }
                    },
                    'headerOptions' => ['style' => 'width:350px'],
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

<?php
$js = <<<JS
    
    //单击整理视频
    $("#arrange").click(function(){
        $(".vk-tabs .pull-right").removeClass("hidden");
        $('input[name="Video[id]"]').removeClass("hidden").prop("checked", false);
    });
        
    //单击取消
    $("#cancel").click(function(){
        $(".vk-tabs .pull-right").addClass("hidden");
        $('input[name="Video[id]"]').addClass("hidden").prop("checked", false);
    });
        
    //单击全选
    $("#allChecked").click(function(){
        $('input[name="Video[id]"]').prop("checked", true);
    });
        
    //单机全不选
    $("#noAllChecked").click(function(){
        $('input[name="Video[id]"]').prop("checked", false);
    });
        
    /**
     * 显示目录模态框  
     * @param {Object} _this
     */
    window.showCatalogModal = function(_this){
        var checkObject = $("input[name='Video[id]']");  
        var val = [];
        for(i in checkObject){
            if(checkObject[i].checked){
               val.push(checkObject[i].value);
            }
        }
        if(val.length > 0){
            showModal(_this.attr("href") + "&move_ids=" + val);
        }else{
            alert("请选择移动的视频");
        }
        return false;
    }   
    
    //设置table的每个tr的跳转链接    
    $('.vk-table > tbody > tr').each(function(){
        var key = $(this).attr('data-key');
        $(this).click(function(event){
            var NodeType = event.target.nodeName;
            if (NodeType == "INPUT") {
                return;
            }
            var a = $('<a href="../video/view?id=' + key + '"' + 'target="_blank" />').get(0);
            var e = document.createEvent('MouseEvents');
            e.initEvent('click', true, true );
            a.dispatchEvent(e);
        });
    });    
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>