<?php

use common\models\vk\UserCategory;
use frontend\modules\cm_material_library\assets\CmMaterialAssets;
use frontend\modules\cm_my_material\assets\CmMyMaterialAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

CmMaterialAssets::register($this);
CmMyMaterialAssets::register($this);

$params = Yii::$app->request->getQueryParams();
$user_cat_id = ArrayHelper::getValue($params, 'user_cat_id', null); //用户分类id
$recursive = !empty(ArrayHelper::getValue($params, 'keyword', '')) ? 1 : 0;  //是否递归搜索（表格显示）
$renderView = $recursive ? '____list' : '____chart';

$params_php = json_encode(array_merge(['page' => 1], $params));     //js参数
$details_dom_php = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
$this->renderFile("@frontend/modules/cm_my_material/views/default/$renderView.php")));

?>

<div class="cm_my_material-default-index material-index ">
    <!--过滤条件-->
    <?= $this->render('_search', [
        'params' => $params,
        'type_id' => $type_id,
        'keyword' => $keyword,
        'user_cat_id' => $user_cat_id,
        'locationPathMap' => $locationPathMap,
    ]) ?>
    
    <!--图表显示素材内容-->
    <div class="lists-content">
        <div class="material-lists">
            <?php if ($recursive == 1) {?>
                <!--总结记录-->
                <div class="summary set-tab">
                    <span>搜索结果： 共搜索到 <b></b> 个素材</span>
                    <a href="javascript:;" onclick="searchF({keyword:''})"> <i class="glyphicon glyphicon-remove-sign"></i></a>
                </div>
                <!--列表显示内容-->
                <div class="meida-table">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 130px">名称</th>
                                <th style="width: 100px">封面</th>
                                <th style="width: 50px">类型</th>
                                <th>所属目录</th>
                                <th style="width: 100px">操作</th>
                            </tr>
                        </thead>
                        <tbody class="meida-details">

                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <!--总结记录-->
                <div class="summary set-tab">
                    <span>共 <b></b> 个素材</span>
                </div>
                <!--目录-->
                <div class="folder">
                    <ul class="list-unstyled">
                        <?php 
                            if($user_cat_id != null){ 
                                $parent_id = UserCategory::getCatById($user_cat_id)->parent_id;  //父级id
                                echo '<li>';
                                    echo Html::a('<i class="ifolder upper-level"></i><p class="folder-name">上一级</p>', 
                                            array_merge(['index'], array_merge($params, ['user_cat_id' => $parent_id > 0 ? $parent_id : null])), ['title' => '上一级']);
                                echo '</li>';
                            }
                            foreach ($userCategoryMap as $category){
                                switch ($category['type']){
                                    case UserCategory::TYPE_SYSTEM: 
                                        $iconFolder = '<i class="ifolder folder-system"></i>';
                                        break;
                                    case UserCategory::TYPE_SHARING:
                                        $iconFolder = '<i class="ifolder folder-share"></i>';
                                        break;
                                    default:
                                        $iconFolder = '<i class="ifolder"></i>';
                                        break;
                                }
                                echo '<li>';
                                    echo Html::a($iconFolder . '<p class="folder-name multi-line-clamp">'. $category['name'] .'</p>',
                                        array_merge(['index'], array_merge($params, ['user_cat_id' => $category['id']])),
                                    ['title' => $category['name'],]);
                                echo '</li>';
                            } 
                        ?>
                    </ul>
                </div>
                <!--内容-->
                <div class="meida-details">

                </div>
            <?php } ?>
            <!--加载-->
            <div class="loading-box">
                <span class="loading" style="display: none"></span>
                <span class="no_more" style="display: none">没有更多了</span>
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
    var plagePageUrl = '/cm_my_material/default/page-list';
    
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
        window.location.href = "/cm_my_material/default/index?"+urlEncode(params).substr(1);
    }
    
    /**
     * 加载数据
     * @param {int} target_page 当前页面
     * @param {int} force       是否强制执行
     * @returns {undefined}
     */
    function listPage(target_page , force){
        page = target_page > total_page ? total_page : target_page;
        // 当前页数是否大于最大页数
        if(!force && target_page > total_page){
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
                $('.summary b').html(data.totalCount);  //设置素材总数
                //console.log(page,total_page);
                //请求成功返回数据，否则提示错误信息
                if(rel['code'] == '0'){
                    for(var i in data.result){
                        var item = $(Wskeee.StringUtil.renderDOM(details_dom_js, data.result[i])).appendTo($(".meida-details"));
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
    
    /**
     * 素材类型更改
     * @returns {void}
     */
    function onTypeChange(){
        var type_id = [];
        $.each($('input[name="type_id[]"]:checked'), function(){
            type_id.push($(this).val()); 
        });
        searchF({type_id: type_id});
    }
    
    /**
     * 对象转url参数
     * @param string url    地址 
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