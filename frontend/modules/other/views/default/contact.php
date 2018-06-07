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
            <p class="kouhao">在线反馈：<?= yii\helpers\Html::a('点击提交', 'feedback') ?></p>
            <p class="kouhao">电子邮件：wulinan@eenet.com</p>
            <p class="kouhao">联系电话：020-83481388转631</p>
            <p class="kouhao">在线QQ客服：361733529</p>
            <p class="kouhao">地址：广州市越秀区麓景西路41号国家开放大学（广州）校区六号楼6楼</p>
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