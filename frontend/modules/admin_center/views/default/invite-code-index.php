<?php

use common\models\vk\Customer;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Customer */

$this->title = Yii::t('app', 'Customer');

?>
<div class="invite-code-index">

     <?= DetailView::widget([
        'model' => $model,
        'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
        'attributes' => [
            [
                'label' => Yii::t('app', 'Invite Code'),
                'format' => 'raw',
                'value' => !empty($model->invite_code) ? Html::input('input', 'inviteCode', $model->invite_code,
                                ['id' => 'inviteCode', 'readonly'=> 'readonly']) : null,
            ],
            [
                'label' => Yii::t('app', '{Already}{Signup}',[
                    'Already' => Yii::t('app', 'Already'), 'Signup' => Yii::t('app', 'Signup')
                ]),
                'format' => 'raw',
                'value' => !empty($totalUser) ? $totalUser . '<span style="color:#999999"> 个</span>' : null,
            ],
        ],
     ])?>
        
</div>

<?php
$js = 
<<<JS
   
JS;
    $this->registerJs($js,  View::POS_READY);
?>
