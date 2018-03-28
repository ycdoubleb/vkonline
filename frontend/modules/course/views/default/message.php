<?php

use common\models\vk\CourseMessage;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model CourseMessage */

//$this->params['breadcrumbs'][] = $this->title;

?>

<ul class="time-vertical">
    
    <?php foreach($dataProvider->models as $model): ?>
    <li>
        <b></b>
        <?= Html::img($model->user->avatar, ['class'=>'img-circle']) ?>
        <div class="arrow"></div>
        <div class="msg-frame">
            <p>
                <span class="username"><?= Html::encode($model->user->nickname) ?></span>
                <span class="time">发于&nbsp;<?= date('Y-m-d H:i:s', $model->created_at) ?></span>
            </p>
            <span>
                <?= $model->content ?>
            </span>
        </div>
    </li>
    <?php endforeach; ?>
    
</ul>

<?php
$js = 
<<<JS
   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>