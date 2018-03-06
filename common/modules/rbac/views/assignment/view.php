<?php

use common\modules\rbac\RbacAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\IdentityInterface;
use yii\web\View;

/* @var $this View */
/* @var $model IdentityInterface */

$this->title = 'Assignment';
$this->params['breadcrumbs'][] = ['label' => 'Assignment', 'url' => ['index']];
?>
<div class="permission-view">

    <?= Html::a('Users', ['index'],['class'=>'btn btn-success']) ?>
    <h1><?= '用户：'.Html::encode($model->{$usernameField}) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            可分配：
            <input id="search-avaliable"><br />
            <select id="list-avaliable" multiple size="15" style="width: 100%">
            </select>
        </div>
        <div class="col-lg-1">
            <br><br>
            <a href="#" id="btn-add" class="btn btn-success" style="width: 100%">&gt;&gt;</a><br>
            <a href="#" id="btn-remove" class="btn btn-danger" style="width: 100%">&lt;&lt;</a>
        </div>
        <div class="col-lg-5">
            已分配：
            <input id="search-assigned"><br />
            <select id="list-assigned" multiple size="15" style="width: 100%">
            </select>
        </div>
    </div>
</div>
<?php
    RbacAsset::register($this);
    $properties = Json::htmlEncode([
        'id'=>$model->{$idField},
        'assignUrl'=>  Url::to('assign'),
        'searchUrl'=>  Url::to('search')
    ]);
    $js = 
<<<JS
wskeee.rbac.initProperties($properties);

$('#search-avaliable').keydown(function(){
    wskeee.rbac.searchRole('avaliable');
});
$('#search-assigned').keydown(function(){
    wskeee.rbac.searchAssign('assigned');
});
$('#btn-add').click(function(){
    wskeee.rbac.addChild("assign");
    return false;
});
$('#btn-remove').click(function(){
    wskeee.rbac.addChild("remove");
    return false;
});
wskeee.rbac.searchRole('avaliable', true);
wskeee.rbac.searchAssign('assigned', true);
JS;
    $this->registerJs($js, View::POS_READY);
?>
