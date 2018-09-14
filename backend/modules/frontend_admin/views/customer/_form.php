<?php

use common\models\vk\Customer;
use kartik\widgets\FileInput;
use kartik\widgets\SwitchInput;
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
    
    <?= $form->field($model, 'short_name')->textInput(['maxlength' => true]) ?>
    
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
    
    <?= $form->field($model, 'is_official')->widget(SwitchInput::class, [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ],
        'containerOptions' => [
            'class' => '',
        ],
    ])->label(Yii::t('app', 'Official'))?>
    
    <?= $form->field($model, 'sort_order')->textInput() ?>

    <?= $form->field($model, 'logo')->widget(FileInput::class, [
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
                        Html::img($model->logo, ['class' => 'file-preview-image', 'width' => '213']),
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
$isNewRecord = $model->isNewRecord ? 1 : 0;

if(!$point || $point['X(location)'] == null){
    $point['X(location)'] = 113.2759952545166;
    $point['Y(location)'] = 23.117055306224895;
}
$map_x = $point['X(location)'];                               //经度
$map_y = $point['Y(location)'];                               //纬度

$js =
<<<JS
    var _self = this;
    var isNewRecord = $isNewRecord;
        
    /** 百度地图设置 */    
    var map = new BMap.Map("map");                                          // 创建地图实例 
    var point = new BMap.Point($map_x,$map_y);                              //地图初始位置

    var myGeo = new BMap.Geocoder();                                        // 创建地址解析器实例
    var marker = new BMap.Marker(point);                                    // 创建标注
    map.addOverlay(marker);                                                 // 将标注添加到地图中
    marker.addEventListener("dragend",onMarkerDragend);
    marker.enableDragging();                                                //设置标注是否可以移动
    //把当前坐标显示出来    
    updateMarker(point.lng , point.lat , isNewRecord ? 12 : 16);            // 初始化地图，设置中心点坐标和地图级别
    
    // 当地址输入框失去焦点时出发事件
    $('#customer-address').change(function(){
        $(this).off('blur');
        $(this).blur(function(){
            // 将地址解析结果显示在地图上,并调整地图视野
            myGeo.getPoint($(this).val(), function(point){
                if (point) {
                    $('#customer-location').val(point.lng + " " + point.lat);
                    updateMarker( point.lng , point.lat , 16);
                }else{
                    alert("您输入的详细地址没有解析到结果!");
                }
            });
        });
    });
        
    /**
     * 更新坐标位置
     * @param px 
     * @param py 
     * @param zoom 地图放大系数
     */
    function updateMarker(px,py,zoom){
        var point = new BMap.Point(px, py);
        map.centerAndZoom(point, zoom);
        marker.setPosition(point);
        $('#customer-location').val(point.lng + " " + point.lat);
    }
    /* 坐标点发生拖拽事件时 */   
    function onMarkerDragend(e){
        //获取marker的位置
        $('#customer-location').val(e.point.lng + " " + e.point.lat);
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