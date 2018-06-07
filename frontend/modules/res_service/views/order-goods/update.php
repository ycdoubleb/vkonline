<?php

use common\models\vk\Course;
use frontend\modules\res_service\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

?>
<div class="order-goods-update main">

    <div class="crumbs">
        <span><?= Yii::t('app', '{Update}{orderGoods}', [
            'Update' => Yii::t('app', 'Update'), 'orderGoods' => Yii::t('app', 'Order Goods')
        ]) ?></span>
    </div>
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php
$js = 
<<<JS
              
    //提交表单    
    $("#submitsave").click(function(){
        $("#order-goods-form").submit();
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>