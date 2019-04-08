<?php

use common\components\aliyuncs\Aliyun;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

GrowlAsset::register($this);

$this->title = Yii::t('app', '{Material}{Preview}',[
    'Material' => Yii::t('app', 'Material'),
    'Preview' => Yii::t('app', 'Preview')
]);

?>

<div class="preview main mc-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                <div class="mc-form clear-shadow">
                    <?php
                        $mediaType = $mediaDetail['type_id'];
                        $mediaUrl = Aliyun::absolutePath($mediaDetail['oss_key']);  //预览路径
                        $cover_url = $mediaDetail['cover_url'];     //封面路径
                        switch ($mediaType){
                            case 1 : 
                                echo '<video src="'.$mediaUrl.'" poster="'.$cover_url.'" id="disabled" controls="controls" controlslist="nodownload" width="100%"></video>';
                                break;
                            case 2 : 
                                echo '<audio src="'.$mediaUrl.'" id="disabled" controls="controls" controlslist="nodownload" style="width:100%"></audio>';
                                break;
                            case 3 : 
                                echo Html::img($mediaUrl, ['id' => "disabled", 'style' => 'width:100%']);
                                break;
                            case 4 : 
                                echo '<iframe src="http://eezxyl.gzedu.com/?furl='.$mediaUrl.'" width="100%" height="700" style="border: none"></iframe>';
                                break;
                        }
                    ?>
                </div>
            </div>
            <div class="modal-footer" style="">
                <div class="media-name"><?= $mediaDetail['name'] . '（编号：' . $mediaDetail['id'] .'）'?></div>
                <div class="media-tags"><?= $mediaDetail['tags']?></div>
            </div>
       </div>
    </div>   
</div>

<?php
$js = <<<JS
 
JS;
    $this->registerJs($js,  View::POS_READY);
?>
