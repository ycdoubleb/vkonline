<?php

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
                        $mediaType = $mediaDetail['type_sign'];
                        $mediaUrl = $mediaDetail['url'];            //预览路径
                        $cover_url = $mediaDetail['cover_url'];     //封面路径
                        switch ($mediaType){
                            case 'video' : 
                                echo '<video src="'.$mediaUrl.'" poster="'.$cover_url.'" id="disabled" controls="controls" controlslist="nodownload" width="100%"></video>';
                                break;
                            case 'audio' : 
                                echo '<audio src="'.$mediaUrl.'" id="disabled" controls="controls" controlslist="nodownload" style="width:100%"></audio>';
                                break;
                            case 'image' : 
                                echo Html::img($mediaUrl, ['id' => "disabled", 'style' => 'max-width:100%']);
                                break;
                            case 'document' : 
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
