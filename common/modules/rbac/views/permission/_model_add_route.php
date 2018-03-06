<?php

use common\modules\rbac\RbacAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/* @var $this View */
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>
<div class="permission-add-route">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= Yii::t('app', 'Add').Yii::t('app/rbac', 'Route') ?></h4>
            </div>
            <div class="modal-body">
                <input class="form-control search" data-target="assigned"
                       placeholder="<?= Yii::t('app/rbac', 'Key Filter'); ?>">
                <select multiple size="20" class="form-control list" data-target="assigned"></select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" aria-label="Close"><?= Yii::t('app', 'Close') ?></button>
                <?= Html::a(Yii::t('app', 'Submit').$animateIcon, ['assign','id'=>$name], [
                        'class' => 'btn btn-success btn-assign',
                        'id' => 'btn-assign',
                        'data-target' => 'assigned',
                    ]);
                    ?>
            </div>
        </div>
    </div> 
</div>

<?php
$assigned = Json::htmlEncode($assigned);
$js = <<<JS
    /**
     * 关键字过滤和列表显示
     */
    $('i.glyphicon-refresh-animate').hide();
        
    var assigned = $assigned;
            
    $('.search[data-target]').keyup(function () {
        search($(this).data('target'));
    });

    function search(target) {
        var list = $('select.list[data-target="' + target + '"]');
        list.html('');
        var q = $('.search[data-target="' + target + '"]').val();
        $.each(assigned, function () {
            var r = this;
            if (r.indexOf(q) >= 0) {
                $('<option>').text(r).val(r).appendTo(list);
            }
        });
    }
        
    //默认先执行一次显示    
    search('assigned');
        
    /**
     * 数据提交
     */
    $('.btn-assign').click(function () {
        var _this = $(this);
        var target = _this.data('target');
        var routes = $('select.list[data-target="' + target + '"]').val();
        if (routes && routes.length) {
            $('i.glyphicon-refresh-animate').show();
            $.post(_this.attr('href'), {items: routes}, function (r) {
                window.location.reload(); 
            }).always(function () {
                $('i.glyphicon-refresh-animate').hide();
            });
        }
        return false;
    });
    
JS;
$this->registerJs($js);
RbacAsset::register($this);
?>