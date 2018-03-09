<?php

use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="build_course-default-index build-course">
    <h1><?= $this->context->action->uniqueId ?></h1>
    <p>
        This is the view content for action "<?= $this->context->action->id ?>".
        The action belongs to the controller "<?= get_class($this->context) ?>"
        in the "<?= $this->context->module->id ?>" module.
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code>
    </p>
</div>
