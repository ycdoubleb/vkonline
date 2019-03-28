<?php

use frontend\modules\cm_material_library\assets\CmMaterialAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

CmMaterialAssets::register($this);
//var_dump($medias);exit;
$params = Yii::$app->request->getQueryParams();
$params_php = json_encode($params); //js参数
$details_dom_php = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
$this->renderFile("@frontend/modules/cm_material_library/views/default/____lists.php")));

?>
<div class="cm_material_library-default-index material-index ">
    <!--过滤条件-->
    <div class="header-top">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => ['id' => 'media-form'],
        ]);?>
        <div class="position-search">
            <div class="position">
                <div class="label-name">当前位置：</div>
                <div class="select-value">
                    <?php $ids = []; foreach ($dirPath as $key => $dir): ?>
                        <?php
                            $ids += [$key => $dir['id']];   //保存目录ID
                            if($dir['id'] == 0){
                                echo Html::a($dir['name'] . " <i></i>", 'javascript:;');
                            } else {
                                echo Html::a($dir['name'] . " <i>×</i>", array_merge(['index'], array_merge($filters, ['dir_id' => $ids[$key - 1]])));
                            }
                        ?>
                        <i class="arrow">&gt;</i>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="search-input">
                <?= Html::input('input', 'keyword', $keyword, ['onblur' => 'searchF({keyword:$(this).val()})'])?>
                <div class="search-icon">
                    <i class="glyphicon glyphicon-search"></i>
                </div>
            </div>
        </div>
        <div class="material-dir">
            <div class="label-name">目录</div>
            <div class="select-value">
                <?php foreach ($dirs as $id => $dir): ?>
                    <a href="javascript:" onclick="searchF({dir_id:<?=$id;?>})"><?= $dir ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="material-type">
            <div class="label-name">类型</div>
            <div class="select-value">
                <?= Html::checkboxList("MediaSearch[type_id]", $type_id, $mediaType, ['onclick' => 'onTypeChange();']);?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    
    <!--图表显示素材内容-->
    <div class="lists-content">
        <div class="material-lists">
            <div class="meida-details">
                
            </div>
            <!--加载-->
            <div class="loading-box">
                <span class="loading" style="display: none"></span>
                <span class="no_more" style="display: none">没有更多了</span>
            </div>

            <!--总结记录-->
            <div class="summary set-bottom">
                <span>共 <b>0</b> 条记录</span>
            </div>
        </div>
    </div>
</div>

<script>
    var page = 0;
    var total_page = 0;
    var pageSize = 20;
    var isPageLoading = false;
    var params_js = <?= $params_php ?>; //js参数
    var details_dom_js = <?= $details_dom_php ?>;
    var plagePageUrl = '/cm_material_library/default/page-list';
    
    window.onload = function(){
        // 搜索图标的点击事件
        $(".search-icon").click(function(){
            searchF({keyword:$(this).val()});
        });
        //滚动事件
        $(window).scroll(function(){
            if($(document).scrollTop() >= $(document).height() - $(window).height() - 300){
                listPage(++page);
            }
        });
        //页面初始后马上云查询第一页数据
        listPage(1,true);
    }
    
    /**
     * 刷新条件
     * @param {key:value} keys  改变的条件
     * @returns {void}
     */
    function searchF(keys){
        var params = $.extend(params_js,keys);
        window.location.href = "/cm_material_library/default/index?"+urlEncode(params).substr(1);
    }
    
    function listPage(target_page , force){
        page = target_page > total_page ? total_page : target_page;
        // 当前页数是否大于最大页数
        if(!force && target_page >= total_page){
            $('.loading-box .loading').hide();
            $('.loading-box .no_more').show();
            return;
        }
        /**
         * 如果页面非加载当中执行
         */
        if(!isPageLoading){
            isPageLoading = true;   //设置已经加载当中...
            var params = $.extend(params_js, {page: target_page});  //传值
            //console.log(params);
            $.get(plagePageUrl, params, function(rel){
                isPageLoading = false;      //取消设置加载当中...
                var data = rel.data;        //获取返回的数据
                
                page = Number(data.page);
                total_page = Number(Math.ceil(data.totalCount / pageSize));
                
                //console.log(page,total_page);
                //请求成功返回数据，否则提示错误信息
                if(rel['code'] == '0'){
                    for(var i in data.result){
                        var item = $(Wskeee.StringUtil.renderDOM(details_dom_js, data.result[i])).appendTo($(".meida-details"));
                        //鼠标经过、离开事件
                        item.hover(function(){
                            $(this).addClass('hover');
                        }, function(){
                            $(this).removeClass('hover');
                        });
                        //点击查看详情
                        item.find(".material-info").click(function(){
                            showModal($(this).attr('data-url'));return false;
                        });
                    }
                    //如果当前页大于最大页数显示“没有更多了”
                    if(page >= total_page){
                        $('.loading-box .no_more').show();
                    }
                }else{
                    $.notify({
                        message: rel['message'],    //提示消息
                    },{
                        type: "danger", //错误类型
                    });
                }
                $('.loading-box .loading').hide();   //隐藏loading
            });
            $('.loading-box .loading').show();
            $('.loading-box .no_more').hide();
        }
    }
    
    function onTypeChange(){
        searchF({type_id:""});
    }
    
    /**
     * 对象转url参数
       @param string url    地址 
     * @param array data    参数对象
     **/
    function urlEncode (param, key, encode) {  
        if(param==null) return '';  
        var paramStr = '';  
        var t = typeof (param);  
        if (t == 'string' || t == 'number' || t == 'boolean') {  
            paramStr += '&' + key + '=' + ((encode==null||encode) ? encodeURIComponent(param) : param);  
        } else {  
            for (var i in param) {  
                var k = key == null ? i : key + (param instanceof Array ? '[' + i + ']' : '.' + i);  
                paramStr += urlEncode(param[i], k, encode);  
            }  
        }  
        return paramStr;  
    }
</script>

<?php


$js = <<<JS
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>