<?php

use frontend\modules\help_center\assets\HelpCenterAssets;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{Opinion}{Feedback}', [
    'Opinion' => Yii::t('app', 'Opinion'),'Feedback' => Yii::t('app', 'Feedback'),
]);

?>

<div class="default-index">
    
    <div class="category-title"><?= $this->title;?></div>
    
    <div class="posts-content">
        
        
    </div>
</div>

<?php

$js = 
<<<JS
        
    $(".post-title").each(function(){
        var elem = $(this);
        if(!elem.next("div.post-content").is(":hidden")){
            elem.css("color","#FF6600");
        }else{
            elem.css("color","#999999");
        };
        elem.click(function(){
            elem.next("div.post-content").toggle();
            if(!elem.next("div.post-content").is(":hidden")){
                elem.css("color","#FF6600");
            }else{
                elem.css("color","#999999");
            };
        })
    }); 
        
JS;
    $this->registerJs($js,  View::POS_READY);
    HelpCenterAssets::register($this);
?>