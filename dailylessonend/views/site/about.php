<?php

/* @var $this View */

use common\widgets\ueditor\UeditorAsset;
use yii\helpers\Html;
use yii\web\View;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;

UeditorAsset::register($this);
var_dump(dirname(Yii::$app->homeUrl));
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>This is the About page. You may modify the following file to customize its content:</p>

    <code><?= __FILE__ ?></code>
    <!--style给定宽度可以影响编辑器的最终宽度-->
    <script type="text/plain" id="myEditor" style="width:1000px;height:240px;">
        <p>这里我可以写一些输入提示</p>
    </script>
</div>
<script type="text/javascript">
    var ue;
    window.UMEDITOR_HOME_URL = '/';
    window.onload = function(){
        //实例化编辑器
        ue = UE.getEditor('myEditor');
    }
   
</script>