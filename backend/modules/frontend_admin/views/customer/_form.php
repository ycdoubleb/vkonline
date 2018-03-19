<?php

use common\models\vk\Customer;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Customer */
/* @var $form ActiveForm */
?>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=purSDv2pGB5wDvymIPfbGGRqdYFBh9VD"></script>

<div class="customer-form">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'customer-form',
        ],
    ]); ?>

    <?php //$form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
    <div class="col-lg-12 col-md-12" style="padding: 0px">
        <div class="col-lg-3 col-md-3" style="padding: 0px 30px 0 0">
            <?= $form->field($model, 'province')->dropDownList($model->getCityList(0),[
                'prompt'=>'--选择省--',
                'onchange'=>'  
                    $(".form-group#customer-province").hide();
                    $.post("'.Yii::$app->urlManager->createUrl('frontend_admin/customer/search-address').'?level=1&parent_id="+$(this).val(),function(data){  
                        $("select#customer-city").html(data);  
                });',  
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-3">
            <?= $form->field($model, 'city')->dropDownList($model->getCityList($model->province),[
                    'prompt'=>'--选择市--',
                    'onchange'=>'
                        $(".form-group#customer-city").show();
                        $.post("'.Yii::$app->urlManager->createUrl('frontend_admin/customer/search-address').'?level=2&parent_id="+$(this).val(),function(data){
                            $("select#customer-district").html(data);
                        });',
                ]) ?>
        </div>
        <div class="col-lg-3 col-md-3">
            <?= $form->field($model, 'district')->dropDownList($model->getCityList($model->city),[
                'prompt'=>'--选择区--',
                'onchange'=>'
                    $(".form-group#customer-district").show();
                    $.post("'.Yii::$app->urlManager->createUrl('frontend_admin/customer/search-address').'?level=3&parent_id="+$(this).val(),function(data){
                        $("select#customer-twon").html(data);
                    });',
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-3">
            <?= $form->field($model, 'twon')->dropDownList($model->getCityList($model->district),[
                'prompt'=>'--选择镇--',
            ]) ?>
        </div>
    </div>
    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'domain')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'logo')->widget(FileInput::classname(), [
        'options' => [
                'accept' => 'image/*',
                'multiple' => false,
            ],
        'pluginOptions' => [
            'resizeImages' => true,
            'showCaption' => false,
            'showRemove' => false,
            'showUpload' => false,
            'browseClass' => 'btn btn-primary btn-block',
            'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
            'browseLabel' => '选择上传Logo...',
            'initialPreview' => [
                $model->isNewRecord ?
                        Html::img('', ['class' => 'file-preview-image', 'width' => '213']) :
                        Html::img(WEB_ROOT . $model->logo, ['class' => 'file-preview-image', 'width' => '213']),
            ],
            'overwriteInitial' => true,
        ],
    ]);?>

    <?= $form->field($model, 'des')->textarea(['rows' => 5]) ?>
    
    <h5 style="font-weight: bold">位置</h5>
    <?= Html::label('','',[
        'id' => 'map',
        'style' => 'width:100%; height:500px; border: 1px solid #d2d6de;',
    ])?>
    <?= Html::activeHiddenInput($model, 'location') ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$isNewRecord = $model->isNewRecord ? 0 : 1;
if($isNewRecord){
    $map_x = $point['X(location)'];                               //经度
    $map_y = $point['Y(location)'];                               //纬度
}else{
    $map_x = 113.2759952545166;
    $map_y = 23.117055306224895;
}
$js =
<<<JS
    var isNewRecord = $isNewRecord,
        map_x = $map_x,
        map_y = $map_y;
        
    /** 百度地图设置 */    
    var map = new BMap.Map("map");                      // 创建地图实例 
    var point = new BMap.Point(113.2759952545166,23.117055306224895);   //地图初始位置
    map.centerAndZoom(point, 12);                       // 初始化地图，设置中心点坐标和地图级别

    var myGeo = new BMap.Geocoder();                    // 创建地址解析器实例
    if(isNewRecord){
        var point = new BMap.Point(map_x, map_y);   
        $('#customer-location').val(map_x + " " + map_y);  //把经纬度传到form表单
        map.centerAndZoom(point, 16);
        var marker = new BMap.Marker(point);    // 创建标注
        map.addOverlay(marker);                 // 将标注添加到地图中
        marker.addEventListener("dragend",onMarkerDragend);
        marker.enableDragging();                //设置标注是否可以移动
        function onMarkerDragend(e){
            //获取marker的位置
            $('#customer-location').val(e.point.lng + " " + e.point.lat);
        }
    }else{
        // 当地址输入框失去焦点时出发事件
        $('#customer-address').blur(function() {
            // 将地址解析结果显示在地图上,并调整地图视野
            myGeo.getPoint($('#customer-address').val(), function(point){
                if (point) {
                    $('#customer-location').val(point.lng + " " + point.lat);
                    map.centerAndZoom(point, 16);
                    var marker = new BMap.Marker(point);    // 创建标注
                    map.addOverlay(marker);                 // 将标注添加到地图中
                    marker.addEventListener("dragend",onMarkerDragend);
                    marker.enableDragging();                //设置标注是否可以移动
                    function onMarkerDragend(e){
                        //获取marker的位置
                        $('#customer-location').val(e.point.lng + " " + e.point.lat);
                    }
                }else{
                    alert("您输入的详细地址没有解析到结果!");
                }
            });
        });
    };
        
    var top_left_navigation = new BMap.NavigationControl(); //左上角，添加默认缩放平移控件
    map.addControl(top_left_navigation);
        
    map.addEventListener("tilesloaded",function(){          //增加监听事件
        map.removeEventListener("tilesloaded",arguments.callee);
        $('#customer-location').trigger('blur');            //获取移动后的位置
    });
        
//    map.enableScrollWheelZoom(true);                      //开启鼠标滚轮缩放
JS;
    $this->registerJs($js,  View::POS_READY); 
?>