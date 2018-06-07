<?php

use frontend\modules\other\assets\OtherAssets;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{About}{We}', [
    'About' => Yii::t('app', 'About'),'We' => Yii::t('app', 'We'),
]);

?>

<div class="default-about other">
    
    <div class="category-title"><?= $this->title;?></div>
    
    <div class="posts-content">
        <img src="/imgs/other/images/about.jpg"/>
    </div>
</div>

<?php

$js = 
<<<JS
        
JS;
    $this->registerJs($js,  View::POS_READY);
    OtherAssets::register($this);
?>