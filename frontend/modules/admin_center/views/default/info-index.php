<?php

use common\models\vk\CustomerAdmin;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CustomerAdmin */

$this->title = Yii::t('app', '{Info}{Prompt}', [
    'Info' => Yii::t('app', 'Info'),
    'Prompt' => Yii::t('app', 'Prompt')
]);

?>
<div class="info-index main vk-modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body" style="color: red; text-align: center">
                <?= Html::encode($info) ?>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary',
                    'data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>

</div>

<?php

$js = 
<<<JS
        
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
