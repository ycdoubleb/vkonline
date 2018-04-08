<?php

/* @var $this yii\web\View */

$this->title = '微课在线平台';
?>

<div class="site-index">

    <div id="carousel-example-generic" class="carousel slide" data-ride="carousel" style="width: 100%; height: 590px">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            <?php foreach ($bannerModel as $index => $model): ?>
            <li data-target="#carousel-example-generic" data-slide-to="<?= $index ?>" class="active"></li>
            <?php endforeach; ?>
        </ol>

        <!-- Wrapper for slides -->
        <div class="carousel-inner" role="listbox">
            <div class="item active">
                <img src="..." alt="...">
                <div class="carousel-caption">
                  ...
                </div>
            </div>
            <div class="item">
                <img src="..." alt="...">
                <div class="carousel-caption">
                  ...
                </div>
            </div>
            ...
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
