<?php

use common\modules\rbac\assets\RouteAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
$this->title = Yii::t('app', '{Route}{Admin}',[
    'Route' => Yii::t('app/rbac', 'Route'),
    'Admin' => Yii::t('app', 'Admin'),
]);
?>

<?php
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>

<div class="rbac-route-index">
    <div class="row">
        <div class="col-sm-11">
            <div class="input-group">
                <input id="inp-route" type="text" class="form-control"
                       placeholder="<?= Yii::t('app', 'Add'); ?>">
                <span class="input-group-btn">
                    <?=
                    Html::a(Yii::t('app', 'Add').$animateIcon, ['create'], [
                        'class' => 'btn btn-success',
                        'id' => 'btn-new',
                    ]);
                    ?>
                </span>
            </div>
        </div>
    </div>
    <p>&nbsp;</p>
    <div class="row">
        <div class="col-sm-5">
            <div class="input-group">
                <input class="form-control search" data-target="available"
                       placeholder="<?= Yii::t('app/rbac', 'Search for available'); ?>">
                <span class="input-group-btn">
                    <?=
                    Html::a('<span class="glyphicon glyphicon-refresh"></span>', ['refresh'], [
                        'class' => 'btn btn-default',
                        'id' => 'btn-refresh',
                    ]);
                    ?>
                </span>
            </div>
            <select multiple size="20" class="form-control list" data-target="available"></select>
        </div>
        <div class="col-sm-1">
            <br><br>
            <?=
            Html::a('&gt;&gt;' . $animateIcon, ['assign'], [
                'class' => 'btn btn-success btn-assign',
                'data-target' => 'available',
                'title' => Yii::t('app/rbac', 'Assign'),
            ]);
            ?><br><br>
            <?=
            Html::a('&lt;&lt;' . $animateIcon, ['remove'], [
                'class' => 'btn btn-danger btn-assign',
                'data-target' => 'assigned',
                'title' => Yii::t('app/rbac', 'Remove'),
            ]);
            ?>
        </div>
        <div class="col-sm-5">
            <input class="form-control search" data-target="assigned"
                   placeholder="<?= Yii::t('app/rbac', 'Search for assigned'); ?>">
            <select multiple size="20" class="form-control list" data-target="assigned"></select>
        </div>
    </div>

<?php
    $post_append = [Yii::$app->getRequest()->csrfParam => Yii::$app->getRequest()->csrfToken];
    $opts = Json::htmlEncode([
        'routes' => $routes,
        'post_append' => $post_append,
    ]);
    $js = <<<JS
        window._opts = {$opts};
        search('available');
        search('assigned');
JS;
    $this->registerJs($js,  View::POS_READY);
    RouteAsset::register($this);
?>