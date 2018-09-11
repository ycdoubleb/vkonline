<?php

use frontend\modules\res_service\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

ModuleAssets::register($this);

?>

<div class="res_service-index main">
    
    <!--面包屑-->
    <div class="crumbs">
        <?= Html::input('input', 'search', '输入品牌名称', ['class' => 'search-brand']) ?>
        <span class="search-icon"><i class="glyphicon glyphicon-search"></i></span>
        <span class="search-result">共 8 条记录</span>
    </div>
    
    <!--数据统计-->
    <div class="panel">
        <div class="list">
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
            <a href="#" class="customer-item" style="background:url(/upload/customer/b92335d9ae00f4865eae3b6411d0313f.jpg?r=5271)">
                <span class="name single-clamp">广东易扬开泰有限公司</span>
            </a>
        </div>
    </div>
</div>