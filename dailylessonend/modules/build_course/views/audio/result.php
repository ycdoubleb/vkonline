<?php

use common\models\vk\UserCategory;
use common\utils\StringUtil;
use dailylessonend\modules\build_course\assets\ModuleAssets;
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

$this->title = Yii::t('app', '{My}{Audio}', [
    'My' => Yii::t('app', 'My'), 'Audio' => Yii::t('app', 'Audio')
]);

?>

<div class="audio-index vk-material main">
    
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
    ]) ?>
    
    <!-- 显示结果 -->
    <div class="vk-tabs">
        <ul class="list-unstyled pull-left">
            <li>
                <span class="summary">
                    搜索结果： 共搜索到 <b><?= $totalCount ?></b> 个音频素材
                    <i class="fa fa-times-circle times-close" aria-hidden="true"></i>
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
                    <?= Html::a(Yii::t('app', 'Confirm'), ['arrange/move', 'table_name' => 'audio'], [
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
                        return Html::checkbox('Audio[id]', false, ['class' => 'hidden', 'value' => $model['id']]);
                    }
                ],
                [
                    'header' => Yii::t('app', '{Preview}{Image}', [
                        'Preview' => Yii::t('app', 'Preview'), 'Image' => Yii::t('app', 'Image'),
                    ]),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width: 137px'],
                    'contentOptions' => ['style' => 'text-align:left; height: 76px'],
                    'format' => 'raw',
                    'value' => function ($model){
                        return Html::img(StringUtil::completeFilePath('/imgs/build_course/images/audio.png'), ['width' => 121, 'height' => 68]);
                    },
                ],
                [
                    'attribute' => 'name',
                    'header' => Yii::t('app', '{Audio}{Name}', [
                        'Audio' => Yii::t('app', 'Audio'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width: 200px'],
                    'contentOptions' => ['style' => 'white-space: unset;'],
                ],
                [
                    'attribute' => 'created_by',
                    'header' => Yii::t('app', 'Created By'),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width: 85px'],
                    'contentOptions' => ['style' => 'white-space: unset;'],
                    'value' => function($model){
                        return $model['nickname'];
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'header' => Yii::t('app', 'Created At'),
                    'filter' => false,
                    'headerOptions' => ['style' => 'width: 105px'],
                    'contentOptions' => ['style' => 'white-space: unset;'],
                    'value' => function($model){
                        return date('Y-m-d H:i', $model['created_at']);
                    },
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

<?php
//用户分类id
$userCatId = ArrayHelper::getValue($filters, 'user_cat_id', null);  
$js = 
<<<JS
    
    //删除搜索条件
    $('.times-close').click(function(){
        $(location).attr({'href': "../audio/index?user_cat_id={$userCatId}"});
    });
        
    //单击整理视频
    $("#arrange").click(function(){
        $(".vk-tabs .pull-right").removeClass("hidden");
        $('input[name="Audio[id]"]').removeClass("hidden").prop("checked", false);
    });
        
    //单击取消
    $("#cancel").click(function(){
        $(".vk-tabs .pull-right").addClass("hidden");
        $('input[name="Audio[id]"]').addClass("hidden").prop("checked", false);
    });
        
    //单击全选
    $("#allChecked").click(function(){
        $('input[name="Audio[id]"]').prop("checked", true);
    });
        
    //单机全不选
    $("#noAllChecked").click(function(){
        $('input[name="Audio[id]"]').prop("checked", false);
    });
        
    /**
     * 显示目录模态框  
     * @param {Object} _this
     */
    window.showCatalogModal = function(_this){
        var checkObject = $("input[name='Audio[id]']");  
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
            var a = $('<a href="../audio/view?id=' + key + '"' + 'target="_blank" />').get(0);
            var e = document.createEvent('MouseEvents');
            e.initEvent('click', true, true );
            a.dispatchEvent(e);
        });
    });    
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>