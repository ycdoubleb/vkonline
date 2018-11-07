<?php use yii\helpers\Html;?>

<li id="{%id%}">
    <div class="head">
        <a href="#toggle_{%id%}" data-toggle="collapse" aria-expanded="false" onclick="replace($(this))">
            <div>
                <i class="fa fa-caret-right"></i>
            </div>
            <span class="name">{%name%}</span>
        </a>
        <div class="icongroup">
            <a href="../knowledge/create?node_id={%id%}" onclick="showModals($(this));return false;">
                <i class="fa fa-plus"></i>
            </a>
            <a href="../course-node/update?id={%id%}" onclick="showModals($(this));return false;">
                <i class="fa fa-pencil"></i>
            </a>
            <?=Html::a('<i class="fa fa-times"></i>', 'javascript:;', [
                'data' => [
                    'pjax' => 0, 
                    'confirms' => Yii::t('app', "{Are you sure}{Delete}【{%name%}】{Node}", [
                        'Are you sure' => Yii::t('app', 'Are you sure '), 
                        'Delete' => Yii::t('app', 'Delete'), 'Node' => Yii::t('app', 'Node') 
                    ]),
                    'method' => 'post',
                    'id' => "{%id%}",
                    'course_id' => "{%course_id%}",
                ],
                'onclick' => 'deleteCourseNode($(this));'
            ]) ?>
            <a href="javascript:;" class="handle"><i class="fa fa-arrows"></i></a>
        </div>
    </div>
    <div id="toggle_{%id%}" class="collapse knowledges" aria-expanded="false">
        <ul id="knowledge" class="sortable list list-unstyled"></ul>
    </div>
</li>

<script type="text/javascript">
    
    function showModals(_this){
        showModal(_this.attr("href"));
    }
    
</script>