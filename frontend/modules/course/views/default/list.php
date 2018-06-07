<?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseAttribute;
use frontend\modules\course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Course */
/* @var $att CourseAttribute */

$this->title = Yii::t('app', 'Course');
$params = Yii::$app->request->getQueryParams();
//分类标题
$cat_times = ['行业分类','一级分类','二级分类','三级分类','四级分类'];
$curCategory = Category::getCatById(ArrayHelper::getValue($params, 'cat_id'));
$categorySelectIds = $curCategory == null ? [0] : explode(',', $curCategory->path);

//属性
//已选属性,格式 ev_attr=id_value@id_value
$ev_attrs = [];
foreach($attrs as $attr){
    $ev_attrs[$attr->id] = null;
}
foreach(explode("@", ArrayHelper::getValue($params, 'ev_attr')) as $ev_item){
    if($ev_item == "")
        continue;
    $itmes = explode('_',$ev_item);
    $ev_attrs[$itmes[0]] = $itmes[1];
}

ModuleAssets::register($this);
?>

<div class="course-default-index">
    <!-- 搜索区 -->
    <div class="filter-result">
        <div class="container content">
            <!-- 搜索结果 -->
            <label class="filter-label">全部结果：</label>
            <div class="filter-control">
                <?php if(!empty($searchTest = ArrayHelper::getValue($params, 'keyword'))): ?>
                <div class="filter-search">
                    <span class="search-text"><?=$searchTest?></span>
                    <span class="search-clear" onclick="searchF({keyword:null})">×</span>
                </div>
                <?php endif; ?>
                <span class="search-count">共 <?= $max_count ?> 条记录</span>
            </div>
        </div>
    </div>
    <div class="filter-box">
        <!-- 搜索条件 -->
        <div class="container filters">
            <div class="filter-row">
                <label class="filter-label">品牌 ：</label>
                <div class="filter-control">
                    <div class="customer-downList">
                        <?= Select2::widget(['name' => 'customer_id', 'data' => $customers, 
                            'value' => ArrayHelper::getValue($params, 'customer_id'),
                            'options' => ['class' => 'form-control', 'placeholder' => '全部' ,'onchange' => 'searchF({customer_id:$(this).val(),cat_id:0})'],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>
                    </div>
                    <span></span>
                </div>
            </div>
            <!-- 分类 -->
            <?php foreach($categoryLevels as $index => $categorys): ?>
            <div class="filter-row">
                <label class="filter-label"><?= $cat_times[$index] ?> ：</label>
                <div class="filter-control">
                    <?php
                        /* 
                         * 什么时候显示选择【全部】？
                         * 可通过获取当前已选择分类的path获取分类的父级路径，
                         * 如 当前已选择A分类，A分类上是顶级分类，A下面还有一级分类，那么$categoryLevels的长度为3，而A.path的长度为3(0,2,26)
                         * 所以A显示选中状态，而A下级的分类即显示选择【全部】
                         * 
                         * 只有最后一排分类才需要考虑是否显示选择【全部】 
                         */
                        $all_active = ($index == count($categoryLevels) - 1 && count($categoryLevels) >= count($categorySelectIds));
                    ?>
                    <a href="javascript:" onclick="searchF({cat_id:<?=$categorySelectIds[$index];?>,ev_attr:null})" class="filter-item filter-all <?= $all_active ? 'active' : '' ?>">全部</a>
                    <?php foreach($categorys as $cid => $cname): ?>
                    <a href="javascript:" onclick="searchF({cat_id:<?=$cid?>,ev_attr:null})" 
                       class="filter-item <?= (isset($categorySelectIds[$index+1]) && $cid == $categorySelectIds[$index+1]) ? 'active' : '' ?>"><?= $cname ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach;?>
        </div>
        <hr id="attr_hr"/>
        <!-- 更多筛选条件 -->
        <div class="container attrs">
            <?php foreach($attrs as $att): ?>
            <div class="filter-row">
                <label class="filter-label"><?= $att->name ?> ：</label>
                <div class="filter-control">
                    <a href="javascript:" onclick="searchAttr({<?= $att->id ?>:null})" class="filter-item filter-all <?= $ev_attrs[$att->id] != null ? '' : 'active' ?>">全部</a>
                    <?php foreach ($att->getValueList() as $value): ?>
                    <a href="javascript:" onclick="searchAttr({<?= $att->id ?>:'<?= $value ?>'})" class="filter-item <?= $ev_attrs[$att->id] == $value ? 'active' : '' ?>"><?= $value ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="attr-switch">更多的筛选条件 ↑</div>
    </div>
    
    
    <!-- 内容区 -->
    <div class="container content">
        <div class="sort">
            <ul>
                <li data-sort="default">
                    <?= Html::a('综合',null,['href' => 'javascript:','onclick'=> new yii\web\JsExpression("searchF({sort:'default'})")]) ?>
                </li>
                <li data-sort="created_at">
                    <?= Html::a('最新',null,['href' => 'javascript:','onclick'=> new yii\web\JsExpression("searchF({sort:'created_at'})")]) ?>
                </li>
                <li data-sort="avg_star">
                    <?= Html::a('口碑',null,['href' => 'javascript:','onclick'=> new yii\web\JsExpression("searchF({sort:'avg_star'})")]) ?>
                </li>
                <li data-sort="learning_count">
                    <?= Html::a('人气',null,['href' => 'javascript:','onclick'=> new yii\web\JsExpression("searchF({sort:'learning_count'})")]) ?>
                </li>
            </ul>
        </div>
        
        <div class="course-list">
        </div>

        <div class="loading-box">
            <span class="loading"></span>
            <span class="no_more">没有更多了</span>
        </div>

    </div>

</div>
    
<?php
$sort = ArrayHelper::getValue($params, 'sort', 'default');      //当前排序
$page_size = 16;                                                //一页显示的数量
$page = ArrayHelper::getValue($params, 'page', 1);              //当前页
$total_page = ceil($max_count/$page_size);                      //最大页数

$params_js = json_encode($params,JSON_FORCE_OBJECT);
$ev_attrs_js = json_encode($ev_attrs);
$js = 
<<<JS
    //----------------------------------------------------------------------------
    //  
    // 当前执行
    //
    //----------------------------------------------------------------------------
    //显示当前排序
    $(".sort ul li[data-sort=$sort]").addClass('active');
        
    /** 提交表单 */
    $('#submit').click(function(){
        $('#course-form').submit();
    });
    
   /**
    * 属性显示与隐藏开关
    **/
    $('.attr-switch').on('click',function(){
        $('.attrs').toggle();
        $(this).html($('.attrs').css('display') == 'none' ? '更多的筛选条件 ↓' : '更多的筛选条件 ↑');
    });
    
    /**
     * 滚屏自动换页
     **/
    var page = $page;               //当前页
    var total_page = $total_page;   //最大数量
    var isPageLoading = false;      //是否换页中
    //课程item项
    var item_dom = '<div class="course-tile">'
                    +'<div class="pic-box">'
                        +'<a href="/course/default/view?id={%id%}" target="_blank"><img src="{%cover_img%}"/></a>'
                    +'</div>'
                    +'<div class="name-box">'
                        +'<span class="name single-clamp" title="{%name%}">{%name%}</span>'
                        +'<span class="content-time">{%content_time%}</span>'
                    +'</div>'
                    +'<div class="tag-box">'
                        +'<span class="tag single-clamp">{%tags%}</span>'
                    +'</div>'
                    +'<div class="customer-box">'
                        +'<span class="customer">{%customer_name%}</span>'
                        +'<span class="leaning">{%learning_count%}人在学</span>'
                    +'</div>'
                    +'<div class="foot-box">'
                        +'<a href="/teacher/default/view?id={%teacher_id%}">'
                            +'<img class="teacher-avatar" src="{%teacher_avatar%}"/>'
                            +'<span class="teacher-name">{%teacher_name%}</span>'
                        +'</a>'
                        +'<span class="star">{%avg_star%} 分</span>'
                    +'</div>'
                +'</div>';
        
    $(window).scroll(function() {
        if($('.loading-box').offset().top - $(document).scrollTop() < $(window).height() + 100 && !isPageLoading){
            searchList(++page);
        }
    });
        
    //页面初始后马上云查询第一页数据
    searchList(1,true);
        
    //----------------------------------------------------------------------------
    //  
    // method
    //
    //----------------------------------------------------------------------------
    /**
     * 搜索课程
     * @param object keys 搜索关键字
     **/
    window.searchF = function (keys){
        var params = $.extend($params_js,keys);
        window.location.href = "/course/default/list?"+urlEncode(params).substr(1);
    }
    
    /**
     * 按属性搜索课程
     * @param object keys 搜索关键字
     **/    
    window.searchAttr = function(keys){
        var ev_attrs = $.extend($ev_attrs_js,keys);
        var ev_attr = '';
        //组安装已选择属性
        for(var id in ev_attrs){
            if(ev_attrs[id] != null){
                ev_attr+=(ev_attr == '' ? '' : '@')+id+"_"+ev_attrs[id];
            }
        }
        searchF({ev_attr:ev_attr});
    }
    
    /**
     * 搜索列表
     * @param int page 指定页
     **/
    function searchList(target_page,force){
        page = target_page > total_page ? total_page : target_page;
        //没有数据
        if(!force && target_page >= total_page){
            $('.loading').hide();
            $('.no_more').show();
            return;
        }
        if(!isPageLoading){
            //设置已经加载当中...
            isPageLoading = true;
            //合并且当前已选择参数
            var params = $.extend($params_js,{page:target_page,size:$page_size});
            //异步执行查询
            $.get('/course/api/search-course',params,function(result){
                isPageLoading = false;
                if(result.code == '200'){
                    //添加查询到的课程
                    $.each(result.data.courses,function(){
                        this.content_time = Wskeee.StringUtil.intToTime(this.content_time,true);
                        var item = $(Wskeee.StringUtil.renderDOM(item_dom,this)).appendTo($('.course-list'));
                        item.hover(
                            function () {
                                $(this).addClass("hover");
                            },
                            function () {
                                $(this).removeClass("hover");
                            }
                        );
                    });
                    if(result.data.page >= total_page){
                        //没有更多了
                        $('.no_more').show();
                    }
                    
                }else{
                    if(console && console.error){
                        console.error(result.data.error);
                    }
                }
                //隐藏loading
                $('.loading').hide();
            });
            $('.loading').show();
            $('.no_more').hide();
        }
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
JS;
    $this->registerJs($js,  View::POS_READY);
?>