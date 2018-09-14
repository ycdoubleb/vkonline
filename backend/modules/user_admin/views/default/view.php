<?php

use common\models\AdminUser;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model User */

$this->title = Yii::t('app', '{User}{Detail}',[
    'User' => Yii::t('app', 'User'),
    'Detail' => Yii::t('app', 'Detail'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <p>
        <?= Html::a('更新', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('分配权限', ['/rbac/user-role/view', 'user_id' => $model->id], [ 'class' => 'btn btn-danger']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'avatar',
                'format' => 'raw',
                'value' => Html::img($model->avatar, ['width' => '140', 'height' => '140']),
               
            ],
            'username',
            'nickname',
            [
                'attribute' => 'sex',
                'value' => $model->sex == 1 ? '男' : '女',
            ],
            'email:email',
            'status',
            'auth_key',
            [
                'attribute' => 'created_at',
                'value' => date('Y-m-d H:i:s', $model->created_at),
            ],
            [
                'attribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s', $model->updated_at),
            ],
        ],
    ]) ?>

</div>
