<?php

use yii\data\ArrayDataProvider;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;
use yii\helpers\Html;
?>
<div class="batch-transcode">
    <?= Html::a('批量转码', 'javascript:;', ['class' => 'btn btn-default btn-tran']) ?>
    <?=
    GridView::widget([
        'dataProvider' => new ArrayDataProvider(['allModels' => $videos, 'key' => 'id', 'pagination' => [
                'pageSize' => 50,
            ],]),
        'columns' => [
            ['class' => CheckboxColumn::className()],
            'name',
            'is_link',
            'mts_status',
            ['value' => function($model) {
                    return "<span class='result'></span>";
                }, 'format' => 'raw', 'label' => '结果'],
        ]
    ]);
    ?>
</div>
<script>
    var $trs = [];
    var isTraning = false;
    var tranIndex = 0;

    window.onload = function () {
        $('.btn-tran').on('click', function () {
            if(isTraning)return;
            $trs = $('tbody input:checkbox:checked').parent().parent();
            startTran();
        });
    }

    function startTran() {
        isTraning = true;
        tran(0);
        $keys = [];
        $.each($trs, function (index) {
            $keys.push($(this).attr('data-key'));
        });

        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "check-transcode-status",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify({vids: $keys}),
                dataType: "json",
                success: function (r) {
                    $.each(r, function () {
                        $('tr[data-key=' + this.id + '] .result').html(this.mts_status);
                    });
                },
                error: function (r) {

                }
            });
        }, 2000);
    }

    function tran(index) {
        tranIndex = index;
        $($trs[index]).find('.result').html('提交转码...');
        $.get('transcode', {vid: $($trs[index]).attr('data-key')}, function () {
            if (tranIndex < $trs.length - 1) {
                tran(tranIndex + 1);
            }else{
                isTraning = false;
                console.log('转码完成');
            }
        });
    }
</script>