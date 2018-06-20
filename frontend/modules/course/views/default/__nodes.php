<?php
/* 
 * 课程目录结构视图 
 */

use common\models\vk\Knowledge;
use yii\web\View;

/* @var $this View */
$finish_percent = $knowledge_count == 0 ? 0 : floor($finish_count / $knowledge_count * 100);

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
                        <?php foreach($node['knowledges'] as $knowledge): ?>
                        <li class="node level_2">
                            <div class="head">
                                <span><?= $knowledge['knowledge_name'] ?></span>
                                <div class="control">
                                    <div class="progress">
                                        <!-- 每个知识点的完成进度 -->
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $knowledge['percent'] ?>" 
                                             aria-valuemin="0" aria-valuemax="100" style="width: <?= $knowledge['percent'] ?>%;">
                                        </div>
                                    </div>
                                    <span class="duration"><?= Knowledge::getKnowledgeResourceInfo($knowledge['knowledge_id'], 'data') ?></span>
                                </div>
                                <a class="btn btn-highlight btn-flat play" href="/study_center/default/view?id=<?= $knowledge['knowledge_id'] ?>" ><?= $knowledge['percent'] > 0 ? '继续学习' : '开始学习' ?></a>
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