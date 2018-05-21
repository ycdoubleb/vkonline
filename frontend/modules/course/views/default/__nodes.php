<?php
/* 
 * 课程目录结构视图 
 */

use common\models\vk\CourseMessage;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
$finish_percent = $video_count == 0 ? 0 : floor($finish_count/$video_count*100);

?>
<div class="c-nodes">
    <div class="panel">
        <div class="panel-body">
            <p>已学习完 <?= $finish_percent ?> %</p>
            <div class="progress">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $finish_percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $finish_percent ?>%;">
                </div>
            </div>
        </div>
    </div>
    <div class="panel node-list">
        <div class="panel-head">课程目录</div>
        <div class="panel-body">
            <ul class="list">
                <!-- 生成节点列表 -->
                <?php foreach($nodes as $node_id => $node): ?>
                <li class="node level_1">
                    <div class="head">
                        <i class="glyphicon glyphicon-th-list"></i>
                        <span><?= $node['node_name'] ?></span>
                    </div>
                    <ul class="list">
                        <!-- 生成视频列表 -->
                        <?php foreach($node['videos'] as $video): ?>
                        <li class="node level_2">
                            <div class="head">
                                <span><?= $video['video_name'] ?></span>
                                <div class="control">
                                    <div class="progress">
                                        <!-- 每个视频的完成进度 -->
                                        <?php $video_finish_percent = $video['is_finish'] ? 100 :  floor($video['finish_time']/$video['duration']*100) ?>
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $video_finish_percent ?>" 
                                             aria-valuemin="0" aria-valuemax="100" style="width: <?= $video_finish_percent ?>%;">
                                        </div>
                                    </div>
                                    <i class="glyphicon glyphicon-play"></i>
                                    <span><?= $video['duration'] ?></span>
                                </div>
                                <a class="btn btn-highlight play"><?= $video['finish_time'] > 0 ? '继续学习' : '开始学习' ?></a>
                            </div>
                        </li>        
                        <?php endforeach; ?>   
                    </ul>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>