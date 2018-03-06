<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
$this->title = "新增用户";
?>
<h1>
    <?php echo $this->title ?>
</h1> 
<div class="create-user">
    <?= $this->render('_form', ['model'=>$model] ) ?>
</div>

