<?php
/* 
 * 课程附件/资源视图 
 */

use common\models\vk\CourseAttachment;
use yii\web\View;

/* @var $this View */
/* @var $attchment CourseAttachment */
?>
<div class="c-attachment">
    <div class="panel">
        <div class="panel-head">
            资源下载（<?= count($attachments) ?>个）
        </div>
        <div class="panel-body">
            <ul class="list">
                <?php foreach($attachments as $attchment): ?>
                <li class="item">
                    <span class="title"><?= $attchment['name'] ?></span>
                    <div class="control-box">
                        <span class="size"><?= Yii::$app->formatter->asShortSize($attchment['size']) ?></span>
                        <i class="glyphicon glyphicon-save"></i>
                    </div>
                    <a class="btn btn-highlight download" href="/webuploader/default/download?file_id=<?= $attchment['id'] ?>" target="_black">
                        <i class="glyphicon glyphicon-save"></i>下载</a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>