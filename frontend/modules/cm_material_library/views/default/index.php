<?php

use frontend\modules\cm_material_library\assets\CmMaterialAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

CmMaterialAssets::register($this);
//var_dump($medias);exit;
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
                <?= Html::input('input', 'keyword', $keyword, [])?>
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
                <span>共 <b><?= $totalCount; ?></b> 条记录</span>
            </div>
        </div>
    </div>
</div>

<script>
    
    /**
     * 
     * @param {type} keys
     * @returns {void}
     */
    function searchF(keys){
        var params = $.extend($params_js,keys);
        window.location.href = "/cm_material_library/default/index?"+urlEncode(params).substr(1);
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
$params = Yii::$app->request->getQueryParams();
$params_js = json_encode($params); //js参数
$page_size = 16;                                                //一页显示的数量
$page = ArrayHelper::getValue($params, 'page', 1);              //当前页

$details_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
$this->renderFile("@frontend/modules/cm_material_library/views/default/____lists.php")));

$js = <<<JS
    /**
     * 滚屏自动换页
     */
    var page = 0; //页数
    var isPageLoading = false;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height() - 300){
            //loaddata(page, '/cm_material_library/default/search');
        }
    });
    //加载第一页的课程数据
    //loaddata(page, '/cm_material_library/default/index');
    /**
     * 加载数据
     * @param int target_page 指定页
     * @param string url 指定的链接
     */
    function loaddata (target_page, url) {
        var maxPageNum =  $totalCount / 20;
        // 当前页数是否大于最大页数
        if(target_page >= Math.ceil(maxPageNum)){
            $('.loading-box .loading').hide();
            $('.loading-box .no_more').show();
            return;
        }
        /**
         * 如果页面非加载当中执行
         */
        if(!isPageLoading){
            isPageLoading = true;   //设置已经加载当中...
            var params = $.extend($params_js, {page: (target_page + 1)});  //传值
            $.get(url, params, function(rel){
                isPageLoading = false;      //取消设置加载当中...
                var data = rel.data;        //获取返回的数据
                page = Number(data.page);   //当前页
                //请求成功返回数据，否则提示错误信息
                if(rel['code'] == '0'){
                    for(var i in data.result){
                        var item = $(Wskeee.StringUtil.renderDOM($details_dom, data.result[i])).appendTo($(".meida-details"));
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
                    if(page >= Math.ceil(maxPageNum)){
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
        
        
    // 搜索图标的点击事件
    $(".search-icon").click(function(){
        searchF({keyword:$(this).val()});
    })
        
    /**
     * 提交表单
     */
    window.submit = function(){
        $('.loading-box .loading').show();
        $('#media-form').submit();
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>