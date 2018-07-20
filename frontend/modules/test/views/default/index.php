<?php

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$name = urlencode('何阳超');
?>

<div class="test-default-index">
    <?= Html::a('清除所有文件', Url::to(['default/clear-file']) ,['class' => 'btn btn-default']) ?>
    <?= Html::a('清除所有分片', Url::to(['default/clear-chunk']) ,['class' => 'btn btn-default']) ?>
    <?= Html::a('打开CourseMarke', Url::to("CourseMaker.Mconline://1cf3a6b67d44d1bf5785147014894ce8/EUvZcoIlx6XpegFyfbty1K9xyMulT9T0/$name"), ['class' => 'btn btn-default']) ?>
    <h3>已上传文件</h3>
    <div>
        <?=
        GridView::widget([
            'dataProvider' => new ArrayDataProvider(['allModels' => $files]),
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'id',
                'name',
                'path',
                'size',
                'created_at:datetime',
                [
                    'format' => 'raw',
                    'value' => function($model){
                        return Html::a('预览',"http://yxonline.tunnel.qydev.com/api/$model->path",['target' => '_blank']);
                    }
                ]
            ],
        ]);
        ?>
    </div>
    
    <h3>已完成的分片</h3>
    <div>
        <?=
        GridView::widget([
            'dataProvider' => new ArrayDataProvider(['allModels' => $chunks]),
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'file_id',
                'chunk_id',
                'chunk_path',
                'chunk_index',
            ],
        ]);
        ?>
    </div>

</div>
