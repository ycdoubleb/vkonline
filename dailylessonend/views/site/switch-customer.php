<?php

use common\components\aliyuncs\Aliyun;
use dailylessonend\assets\SiteAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

SiteAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t('app', '{Switch}{Customer}', [
    'Switch' => Yii::t('app', 'Switch'), 'Customer' => Yii::t('app', 'Customer')
]);

?>

<div class="switch-customer">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body site-index">
                <!--入驻伙伴-->
                <div class="partner">
                        <div class="list" style="margin-right: -5px; text-align: center">
                            <?php foreach ($customers as $customer): $condition = $customer['id'] == Yii::$app->user->identity->customer_id; ?>
                            <a data-id="<?= $customer['id'] ?>" class="<?= $condition ? 'disabled' : '' ?>" onclick="switchCustomer($(this).attr('data-id'));" style="text-decoration: none; cursor: pointer">
                                <div class="customer-item" style="border:1px #999999 solid; margin-right: 3px; background-image:url(<?= Aliyun::absolutePath($customer['logo']) ?>)">
                                    <span class="name single-clamp"><?= $customer['name'] ?></span>
                                    <?php if($condition): ?>
                                    <div class="active icon"><i class="glyphicon glyphicon-ok"></i></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                </div>
            </div>
       </div>
    </div>
    
</div>

<script type="text/javascript">
    /**
     * 切换品牌
     * @param {string} _dataID
     * @returns {undefined}
     */
    function switchCustomer(_dataID){
        $.post('/site/switch-customer', {customer_id: _dataID}, function(res){
            hideModal();
            if(res.code == 200){
                location.replace(location.pathname);
            }
            $.notify({
                message: res.message,
            },{
                type: res.code == 200 ? "success " : "danger",
            });
        });
    }
</script>
