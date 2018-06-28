<?php

use frontend\modules\help_center\assets\HelpCenterAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{Help}{Center}', [
    'Help' => Yii::t('app', 'Help'),'Center' => Yii::t('app', 'Center'),
]);

?>

<div class="default-index">
    
    <div class="category-title"><?= $categoryName['name']?></div>
    
    <div class="posts-content">
        
        <?php foreach($posts as $key => $post): ?>
            <div class="post-title">
                <?php
                    $title = $key+1 . '、' . $post['title'];
                    echo $title;
                ?>
            </div>

            <div class="post-content" style="<?= $key > 0 ? 'display: none;' : '' ;?>">
                <?= Html::decode($post['content'])?>
            </div>
        
        <?php endforeach; ?>
        
    </div>
    <div class="footer-kefu">
        <div class="kehu-title">在线客服</div>
        <div class="telphone-content">
            <div class="phone">
                <span class="icon-phone"></span>
                <p class="title">020-83481388转631</p>
            </div>
            <div class="QQ">
                <span class="icon-qq"></span>
                <p class="title"><a href="javascrip:;">在线客服QQ:361733529</a></p>
            </div>
        </div>
        <div class="time">
            <p class="time">客服在线时间周一至周五：9:30-12:00,13:30-18:00</p>
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