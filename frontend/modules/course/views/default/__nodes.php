<?php

use common\models\vk\CourseMessage;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
?>
<div class="c-nodes">
    <div class="panel">
        <div class="panel-body">
            <p>已学习完<?= 48 ?>%</p>
            <div class="progress">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                </div>
            </div>
        </div>
    </div>
    <div class="panel node-list">
        <div class="panel-head">课程目录</div>
        <div class="panel-body">
            <ul class="list">
                <li class="node level_1">
                    <div class="head">
                        <i class="glyphicon glyphicon-th-list"></i>
                        <span>第一章 从实验学化学（5）</span>
                    </div>
                    <ul class="list">
                        <li class="node level_2">
                            <div class="head">
                                <span>化学实验基本方法简介</span>
                                <div class="control">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                                        </div>
                                    </div>
                                    <i class="glyphicon glyphicon-play"></i>
                                    <span>08:52</span>
                                </div>
                                <a class="btn btn-primary play">开始学习</a>
                            </div>
                        </li>
                        <li class="node level_2">
                            <div class="head">
                                <span>物质的量的概念</span>
                                <div class="control">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                                        </div>
                                    </div>
                                    <i class="glyphicon glyphicon-play"></i>
                                    <span>08:52</span>
                                </div>
                                <a class="btn btn-primary play">开始学习</a>
                            </div>
                        </li>
                        <li class="node level_2">
                            <div class="head">
                                <span>物质的量的概念的运用</span>
                                <div class="control">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                                        </div>
                                    </div>
                                    <i class="glyphicon glyphicon-play"></i>
                                    <span>08:52</span>
                                </div>
                                <a class="btn btn-primary play">开始学习</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <li class="node level_1">
                    <div class="head">
                        <i class="glyphicon glyphicon-th-list"></i>
                        <span>第二章 化学物质及其变化（11）</span>
                    </div>
                </li>
                <li class="node level_1">
                    <div class="head">
                        <i class="glyphicon glyphicon-th-list"></i>
                        <span>第三章 金属及其化合物（8）</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>


<?php
$js = <<<JS
   
        
JS;
$this->registerJs($js, View::POS_READY);
?>