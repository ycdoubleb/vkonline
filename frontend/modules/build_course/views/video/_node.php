<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

?>

<li id="{%id%}">
    <div class="head gray">
        <a href="#toggle_{%id%}" data-toggle="collapse" aria-expanded="false"><i class="fa fa-play-circle"></i><span class="name">{%name%}</span></a>
        <i class="glyphicon glyphicon-link is_ref" style="display: {%is_ref%}"></i>
        <div class="icongroup">
            <a href="../video/view?id={%id%}" target="_blank"><i class="fa fa-eye"></i></a>
            <a href="../video/update?id={%id%}" onclick="showModal($(this));return false;"><i class="fa fa-pencil"></i></a>
            <a href="../video/delete?id={%id%}" onclick="showModal($(this));return false;"><i class="fa fa-times"></i></a>
            <a href="javascript:;" class="handle"><i class="fa fa-arrows"></i></a>
        </div>
    </div>
    <div id="toggle_{%id%}" class="collapse" aria-expanded="false"></div>
</li>