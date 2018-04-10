<?php

use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

$this->title = '微课在线平台';
?>

<style type="text/css">
    
    .carousel-inner > .item > video, .carousel-inner > .item > a > video {
        object-fit: fill;
    }
</style>

<div class="site-index">

    <div id="carousel-example-generic" class="carousel slide" data-ride="carousel" style="width: 100%; height: 590px">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            <?php foreach ($bannerModel as $index => $model): ?>
            <li data-target="#carousel-example-generic" data-slide-to="<?= $index ?>" class="<?= $index == 0 ? 'active' : '' ?>"></li>
            <?php endforeach; ?>
        </ol>

        <!-- Wrapper for slides -->
        <div class="carousel-inner" role="listbox">
            <?php foreach ($bannerModel as $index => $model): ?>
            <div class="item <?= $index == 0 ? 'active' : '' ?>">
                <?php if($model->type == 1): ?>
                <img src="<?= $model->path ?>" width="100%" style="height: 590px;">
                <?php else: ?>
                <video src="<?= $model->path ?>" autoplay loop width="100%" height="590"></video>
                <?php endif; ?>
                <div class="carousel-caption"></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Controls -->
        <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
    
</div>
