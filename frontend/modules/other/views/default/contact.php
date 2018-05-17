<?php

use frontend\modules\help_center\assets\HelpCenterAssets;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{Contact}{We}', [
    'Contact' => Yii::t('app', 'Contact'),'We' => Yii::t('app', 'We'),
]);

?>

<div class="default-index">
    
    <div class="category-title"><?= $this->title;?></div>
    
    <div class="posts-content">
        <div class="post-title">
                客户服务（支付问题、功能故障、投诉建议、使用帮助）
        </div>
        
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