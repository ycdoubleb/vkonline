<?php

use frontend\modules\help_center\assets\HelpCenterAssets;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{About}{We}', [
    'About' => Yii::t('app', 'About'),'We' => Yii::t('app', 'We'),
]);

?>

<div class="default-index">
    
    <div class="category-title"><?= $this->title;?></div>
    
    <div class="posts-content">
        <p>游学吧的介绍....</p>
        <p>&nbsp;</p>

        <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;游学吧致力为广大热爱学习的小伙伴提供线上及线下的精品美学类课程，课程目前以摄影技
        术及摄影后期为主，日后将扩充到美学及设计的方方面面，可以帮助您随时随地学习提高。专家太累，大师太远，游学吧信奉达人精神，
        分享可以实践的亲和知识，将千里之行放在脚下</p>
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