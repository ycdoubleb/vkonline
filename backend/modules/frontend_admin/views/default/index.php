<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */

$this->title = Yii::t('app', 'Survey');

?>

<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=purSDv2pGB5wDvymIPfbGGRqdYFBh9VD"></script>
<script type="text/javascript" src="http://api.map.baidu.com/library/TextIconOverlay/1.2/src/TextIconOverlay_min.js"></script>
<script type="text/javascript" src="http://api.map.baidu.com/library/MarkerClusterer/1.2/src/MarkerClusterer_min.js"></script>

<div class="frontend_admin-default-index customer">
    <!--客户分布图-->
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-map-marker"></i>
            <span> <?= Yii::t('app', '{Customer}{Distribution}（' . count($customerInfo) . '）',[
                'Customer' => Yii::t('app', 'Customer'),
                'Distribution' => Yii::t('app', 'Distribution'),
            ]) ?></span>
        </div>
        <div id="map" class="map"></div>
    </div>
    <!--客户及用户的数量-->
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-user"></i>
            <span><?= Yii::t('app', '{Customer}/{User}',[
                'Customer' => Yii::t('app', 'Customer'),
                'User' => Yii::t('app', 'User'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $totalUser,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'label' => Yii::t('app', 'Customer'),
                    'format' => 'raw',
                    'value' => !empty($customerInfo) ? count($customerInfo) : '0' . ' 个',
                ],
                [
                    'label' => Yii::t('app', 'User'),
                    'format' => 'raw',
                    'value' => !empty($totalUser) ? $totalUser : '0' . ' 个',
                ],
            ],
        ]) 
         ?>
    </div>
    <!--存储信息-->
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-database"></i>
            <span><?= Yii::t('app', 'Storage') ?></span>
        </div>
    </div>
    <!--资源统计-->
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-line-chart"></i>
            <span><?= Yii::t('app', '{Resources}{Statistics}',[
                'Resources' => Yii::t('app', 'Resources'),
                'Statistics' => Yii::t('app', 'Statistics'),
            ]) ?></span>
        </div>
    </div>
</div>

<?php

$map = [];
foreach ($customerInfo as $key => $sceneInfo){
    $map_x = $sceneInfo['X(location)'];                 //经度
    $map_y = $sceneInfo['Y(location)'];                 //纬度
    $map_address = $sceneInfo['address'];               //地址
    $map[] = [
        'x' => $map_x,
        'y' => $map_y,
        'ads' => $map_address,
    ];
}   
$maps = json_encode($map); 

$js = <<<JS
    // 百度地图API功能	
    map = new BMap.Map("map");
    var point_first = new BMap.Point(105.880746, 35.95393);   //地图初始位置
    setTimeout(function(){
        map.centerAndZoom(point_first, 5);
    },2000);
    map.centerAndZoom(point_first, 4);
    
    var data_info = $maps;
    var markers = [];
    var point = null;
    for (var i in data_info) {
        var point = new BMap.Point(data_info[i].x, data_info[i].y);
        var marker = new BMap.Marker(point);
        var content = data_info[i].ads;
        addClickHandler(content, marker); //添加点击事件
        markers.push(marker);
    };
    //最简单的用法，生成一个marker数组，然后调用markerClusterer类即可。
    var markerClusterer = new BMapLib.MarkerClusterer(map, {
        markers:markers,
    });

    var opts = {
        width : 200,            // 信息窗口宽度
        height: 60,             // 信息窗口高度
        title : "地址：" ,       // 信息窗口标题
        enableMessage:true      //设置允许信息窗发送短息
    };
    function addClickHandler(content,marker){       //点击事件
        marker.addEventListener("click",function(e){
                openInfo(content,e)}
        );
    };
    function openInfo(content,e){
        var p = e.target;
        var point = new BMap.Point(p.getPosition().lng, p.getPosition().lat);
        var infoWindow = new BMap.InfoWindow(content,opts); // 创建信息窗口对象 
        map.openInfoWindow(infoWindow,point);               //开启信息窗口
    }; 
        
    var top_left_navigation = new BMap.NavigationControl(); //左上角，添加默认缩放平移控件
    map.addControl(top_left_navigation);
    //map.enableScrollWheelZoom(true);                        //开启鼠标滚轮缩放
  
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>