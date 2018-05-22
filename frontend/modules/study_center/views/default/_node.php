<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="node-list">
    <div class="node-body">
        <ul class="list">
            <!-- 生成节点列表 -->
            <?php foreach($nodes as $node_id => $node): ?>
            <li class="level_1">
                <div class="head">
                    <i class="fa fa-list-ul"></i>
                    <span><?= $node['node_name'] ?></span>
                </div>
                <ul class="list">
                    <!-- 生成视频列表 -->
                    <?php foreach($node['videos'] as $video): ?>
                    <li class="level_2">
                        <?php 
                            $id = ArrayHelper::getValue(Yii::$app->request->queryParams, 'id');
                            $is_found = $video['video_id'] == $id
                        ?>
                        <?= Html::beginTag('a', ['href' => Url::to(['view', 'id' => $video['video_id']])]) ?>
                            <div class="head <?= $is_found ? 'active' : '' ?>">
                                <i class="fa <?= $is_found ? 'fa-play-circle' : '' ?>"></i>
                                <span><?= $video['video_name'] ?></span>
                                <div class="control">
                                    <div class="progress">
                                        <!-- 每个视频的完成进度 -->
                                        <?php $video_finish_percent = $video['is_finish'] ? 100 :  floor($video['finish_time']/$video['duration']*100) ?>
                                        <div class="progress-bar progress-bar-success" style="width: <?= $video_finish_percent ?>%;">
                                        </div>
                                    </div>
                                    <span><?= $video['duration'] ?></span>
                                </div>
                            </div>
                        <?= Html::endTag('a') ?>
                    </li>        
                    <?php endforeach; ?>   
                </ul>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>