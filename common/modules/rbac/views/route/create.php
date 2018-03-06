<?php

use common\common\modules\rbac\assets\RouteAsset;
use kartik\widgets\Select2;
use common\modules\rbac\models\Route;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;


/* @var $this View */
/* @var $route Route */

$this->title = Yii::t('app', 'Create').Yii::t('app/rbac', 'Route');
$this->params['breadcrumbs'][] = ['label' => 'Auth Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rbac-route-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <label><?= Yii::t('app/rbac', 'System_ID')?>：</label>
        <?php 
            echo Select2::widget([
            'name' => 'system_id',
            'data' => $categorys,
            'options' => [
                'placeholder' => '请选择 ...',
            ],
        ]);
        ?>
    </p>
    
    <div>
        <label>关键字：</label>
        <input id="filter" class="filter"/>
        <?= Html::submitButton("刷新",['class'=>'btn btn-default']) ?>
    </div>
    
    <div class="list">
        
    </div>
    <div>
        <div><input id="list-all" class="list-all" type="checkbox"/><label for="list-all">全选</label></div>
        <?= Html::submitButton("提交",['id'=>'submit','class'=>'btn btn-success']) ?>
    </div>
    
</div>

<?php
    $searchURL = Url::to('route/search');
    $routes = json_encode($routes['available']);
    
    $js = <<<JS
        var routes = $routes;
         
        $('#filter').blur( function () { 
            filter(routes,this.value);
            $('.list-all').prop('checked',false);
        });
        $("#filter").keyup(function(data){
            if(data['keyCode'] == 13){
                filter(routes,this.value);
                $('.list-all').prop('checked',false);
            }
        });
        $('.list-all').change(function(){
            $('.list :input').prop('checked',$(this).prop('checked'));
        });
            
        $('#submit').click(function(){
            
        });
        filter(routes,'');
            
JS;
    RouteAsset::register($this);
    $this->registerJs($js);
?>