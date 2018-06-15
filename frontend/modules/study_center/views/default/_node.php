<?php

use common\utils\DateUtil;
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
                <div class="head single-clamp">
                    <i class="fa fa-list-ul"></i>
                    <span><?= $node['node_name'] ?></span>
                </div>
                <ul class="list">
                    <!-- 生成视频列表 -->
                    <?php foreach($node['knowledges'] as $knowledge): ?>
                    <li class="level_2">
                        <?php $id = ArrayHelper::getValue($params, 'id'); ?>
                        <?= Html::beginTag('a', ['href' => Url::to(['view', 'id' => $knowledge['knowledge_id']])]) ?>
                            <div class="head single-clamp <?= $knowledge['knowledge_id'] == $id ? 'active' : '' ?>">
                                <i class="fa <?= $knowledge['knowledge_id'] == $id ? 'fa-play-circle' : ''  ?>"></i>
                                <span><?= $knowledge['knowledge_name'] ?></span>
                                <div class="control">
                                    <div class="progress">
                                        <!-- 每个视频的完成进度 -->
                                        <div class="progress-bar progress-bar-success" style="width: <?= $knowledge['percent'] ?>%;">
                                        </div>
                                    </div>
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