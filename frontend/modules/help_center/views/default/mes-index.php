<?php

use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{Hlep}{Center}',[
    'Hlep' => Yii::t('app', 'Hlep'),
    'Center' => Yii::t('app', 'Center'),
]);
$this->params['breadcrumbs'][] = $this->title;
?>

<ul class="time-vertical">
    
    <?php foreach($dataProvider AS $item): ?>
    <li>
        <b></b>
        <?= Html::img(WEB_ROOT.$item['avatar'], ['class'=>'img-circle']) ?>
        <div class="mes-frame">
            <p>
                <span class="username"><?= Html::encode($item['nickname']) ?></span>
                <span class="time">发于<?= date('Y-m-d H:i:s', $item['created_at']) ?></span>
            </p>
            <span>
                <?= $item['content'] ?>
            </span>
        </div>
    </li>
    <?php endforeach; ?>
    
</ul>
    

<?php
$js = 
<<<JS
   
        
JS;
    //$this->registerJs($js,  View::POS_READY);
?>

<?php
    //McbsAssets::register($this);
?>