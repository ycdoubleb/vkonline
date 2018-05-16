<?php

use common\models\vk\Video;
use frontend\modules\video\assets\ModuleAssets;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Video */

$this->title = Yii::t('app', "{Add}{Video}",[
    'Add' => Yii::t('app', 'Add'), 'Video' => Yii::t('app', 'Video')
]);

?>

<style type="text/css">
    .video-reference .form-horizontal .form-group {
        margin-right: 0;
        margin-left: 0;
    }
    .video-reference .field-video-is_ref .form-group {
        margin-bottom: 0;
    }
    .video-reference .form-group .form-label {
        padding: 10px 0; color: #999; font-weight: bold; 
    }
</style>

<div class="video-reference">
    <!--引用视频开关-->
    <div class="form-horizontal">
        <div class="form-group field-video-is_ref">
            <?= Html::label(Yii::t('app', '{Reference}{Video}', [
                'Reference' => Yii::t('app', 'Reference'), 'Video' => Yii::t('app', 'Video')
            ]), 'video-is_ref', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
            <div class="col-lg-6 col-md-6">
                <?= SwitchInput::widget([
                    'id' => 'video-is_ref',
                    'name' => 'video-is_ref',
                    'value' => 1,
                    'pluginOptions' => [
                        'handleWidth' => 20,
                        'onText' => 'Yes',
                        'offText' => 'No',
                    ],
                    'pluginEvents' => [
                        "switchChange.bootstrapSwitch" => "function(event, state) { switchLog(event, state) }",
                    ],
                ]) ?>
            </div>
            <div class="col-lg-6 col-md-6"><div class="help-block"></div></div>
        </div>
    </div>
    <!-- 排序 -->
    <div class="sort">
        <ul class="keep-right">
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at'])), ['id' => 'zan_count', 'data-sort' => 'zan_count']) ?>
            </li>
        </ul>
    </div>
    
</div>

<?php
$js = 
<<<JS
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
