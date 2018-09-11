<?php

use common\models\vk\Course;
use frontend\modules\res_service\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = '课程资源：';

?>

<div class="from-course_info main">
    <div class="crumbs">
        <?= $this->title?>
        <span>
            <?= $catFullPath[$id] . ' > ' . Course::findOne($id)->name?>
        </span>
        <div class="framebtn">
            <?= Html::button('导出表格', ['class' => 'export-btn btn btn-success'])?>
        </div>
    </div>
    <div class="vk-panel">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered detail-view vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                [
                    'class' => 'frontend\modules\res_service\components\CourseInfoListRowspan',
                    'label' => Yii::t('app', 'Node'),
                    'value' => function ($data){
                        return $data['node_name'];
                    },
                    'headerOptions' => ['style' => 'width:200px'],
                ],
                [
                    'label' => Yii::t('app', 'Knowledge'),
                    'value' => function ($data){
                        return $data['knowledge_name'];
                    },
                    'headerOptions' => ['style' => 'width:260px'],
                    'contentOptions' => ['style' => 'white-space:normal'],
                ],
                [
                    'label' => Yii::t('app', '复制视频路径'),
                    'format' => 'raw',
                    'value' => function ($data){
                        $has_source = isset($data['video_source']['source_video']); //是否有原视频地址
                        $has_ld = isset($data['video_source']['ld_video']);  //是否有流畅视频地址
                        $has_sd = isset($data['video_source']['sd_video']);  //是否有标清视频地址
                        $has_hd = isset($data['video_source']['hd_video']);  //是否有高清视频地址
                        $has_fd = isset($data['video_source']['fd_video']);  //是否有超清视频地址
                        return Html::button(Yii::t('app', '原视频'), ['data-clipboard-text' => $has_source ? $data['video_source']['source_video'] : '',
                                'class' => 'copy-link btn btn-links' . ($has_source ? '' : ' disabled')]).
                            Html::button(Yii::t('app', '流畅'), ['data-clipboard-text' => $has_ld ? $data['video_source']['ld_video'] : '',
                                'class' => 'copy-link btn btn-links' . ($has_ld ? '' : ' disabled')]).
                            Html::button(Yii::t('app', '标清'), ['data-clipboard-text' => $has_sd ? $data['video_source']['sd_video'] : '',
                                'class' => 'copy-link btn btn-links' . ($has_sd ? '' : ' disabled')]).
                            Html::button(Yii::t('app', '高清'), ['data-clipboard-text' => $has_hd ? $data['video_source']['hd_video'] : '',
                                'class' => 'copy-link btn btn-links' . ($has_hd ? '' : ' disabled')]).
                            Html::button(Yii::t('app', '超清'), ['data-clipboard-text' => $has_fd ? $data['video_source']['fd_video'] : '',
                                'class' => 'copy-link btn btn-links' . ($has_fd ? '' : ' disabled')]);
                    }
                ]
            ]
        ])?>
        
    </div>
</div>

<?php

$js = <<<JS
    
    //点击复制视频地址
    var btns = document.querySelectorAll('button');
    var clipboard = new ClipboardJS(btns);
    clipboard.on('success', function(e) {
        $.notify({
            message: '复制成功',
        },{
            type: "success",
        });
    });
    clipboard.on('error', function(e) {
        $.notify({
            message: '复制失败',
        },{
            type: "danger",
        });
    });

    //导出表格
    $(".export-btn").click(function(){
        location.href = "/res_service/export/single?id=$id";
    })
JS;
    $this->registerJs($js, View::POS_READY);
?>