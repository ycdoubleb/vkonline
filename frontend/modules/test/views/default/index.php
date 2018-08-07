<?php

use common\models\User;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$user = User::findOne(['id' => '1cf3a6b67d44d1bf5785147014894ce8']);
$name = base64_encode($user->nickname);
?>

<div class="test-default-index">
    <?= Html::a('清除所有文件', Url::to(['default/clear-file']) ,['class' => 'btn btn-default']) ?>
    <?= Html::a('清除所有分片', Url::to(['default/clear-chunk']) ,['class' => 'btn btn-default']) ?>
    <?= Html::a('打开CourseMarke', Url::to("CourseMaker.Mconline://{$user->id}/{$user->access_token}/$name"), ['class' => 'btn btn-default']) ?>
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
                        return Html::a('预览', common\components\aliyuncs\Aliyun::absolutePath($model->oss_key),['target' => '_blank']);
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
