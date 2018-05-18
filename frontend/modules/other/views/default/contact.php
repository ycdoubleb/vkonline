<?php

use frontend\modules\other\assets\OtherAssets;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{Contact}{We}', [
    'Contact' => Yii::t('app', 'Contact'),'We' => Yii::t('app', 'We'),
]);

?>

<div class="default-contact other">
    
    <div class="category-title"><?= $this->title;?></div>
    
    <div class="posts-content">
        <div class="post-title">客户服务（支付问题、功能故障、投诉建议、使用帮助）</div>
        <div class="post-content">
            <p class="kouhao">电子邮件：kefu@eenet.com</p>
            <p class="kouhao">在线反馈：点击提交</p>
            <p class="kouhao">联系电话：010-52013140</p>
            <p class="kouhao">在线QQ客服：123456789</p>
            <p class="kouhao"><span>客服提醒：</span>客服在线时间工作日周一至周五9:30-12:00,13:30-18:00为了更快帮您
                解决问题，请在邮件中留下电话、高高手账号、昵称、订单号、截图等信息，并尽可能详细的描述问题。</p>
        </div>

        <div class="post-title">培训机构、讲师合作</div>
        <div class="post-content">
            <p>如果你是某方面的高手，有兴趣分享达人经验，请联系</p>
            <p class="emil">电子邮件：fenxiang@eenet.com</p>
            <p class="kouhao">市场商务合作</p>
            <p>如果你有任何品牌、商务等的市场合作意向，请联系</p>
            <p class="emil">电子邮件：hezuo@eenet.com</p>
            <p class="kouhao">加入我们</p>
            <p>如果你想要和我们一起工作，可将您的简历投递至</p>
            <p class="emil">电子邮件：zhaoping@eenet.com</p>
            <p class="kouhao">通讯地址</p>
            <p>地址：广州市越秀区麓景西路48号广州广播电视大学6号楼6楼</p>
            <p class="emil">邮编：510091</p>
        </div>
    </div>
</div>

<?php

$js = 
<<<JS
        
JS;
    $this->registerJs($js,  View::POS_READY);
    OtherAssets::register($this);
?>