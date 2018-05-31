<?php

use common\widgets\tags\TagWidget;
use yii\helpers\Html;
    
    /* @var $tags TagWidget */
    
    echo Html::dropDownList($tags->name, $tags->value, $tags->data, $tags->options);
    
?>

