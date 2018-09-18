<?php

use common\components\aliyuncs\Aliyun;
use frontend\modules\res_service\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

ModuleAssets::register($this);

?>

<div class="res_service-index main">
    
    <!--面包屑-->
    <div class="crumbs">
        <?php $form = ActiveForm::begin([
            'action' => ['to-index'],
            'method' => 'get',
            'options'=>[
                'id' => 'brand-authorize-form',
                'class'=>'form-horizontal',
            ]
        ]); ?>
            <?= Html::input('input', 'BrandAuthorizeSearch[brand_to]',
                    ArrayHelper::getValue($params, 'BrandAuthorizeSearch.brand_to'),[
                        'placeholder' => '请输入品牌名称',
                        'onchange' => 'submitForm();',
                        'class' => 'search-brand'
                    ]) ?>
            <span class="search-icon"><i class="glyphicon glyphicon-search"></i></span>
            <span class="search-result">共 <?= count($dataProvider->models);?> 条记录</span>
        <?php ActiveForm::end(); ?>
    </div>
    
    <!--数据统计-->
    <div class="panel">
        <div class="list">
            <?php foreach ($dataProvider->models as $data):?>
                <div class="customer-item" style="background:url(<?= Aliyun::absolutePath($data['logo']) ?>)">
                    <span class="name single-clamp"><?= $data['name'] ?></span>
                </div>
            <?php endforeach;?>
        </div>
    </div>
</div>