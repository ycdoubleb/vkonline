<?php use yii\helpers\Html;?>

<li id="{%id%}">
    <div class="head">
        <a>
            <span class="name">{%name%}</span>
            <span class="data">{%data%}</span>
        </a>
        <div class="icongroup">
            <a href="/study_center/default/view?id={%id%}" target="_blank">
                <i class="fa fa-eye"></i>
            </a>
            <a href="../knowledge/update?id={%id%}" onclick="showModals($(this));return false;">
                <i class="fa fa-pencil"></i>
            </a>
            <?=Html::a('<i class="fa fa-times"></i>', 'javascript:;', [
                'data' => [
                    'pjax' => 0, 
                    'confirms' => Yii::t('app', "{Are you sure}{Delete}【{%name%}】{Knowledge}", [
                        'Are you sure' => Yii::t('app', 'Are you sure '), 
                        'Delete' => Yii::t('app', 'Delete'), 'Knowledge' => Yii::t('app', 'Knowledge')
                    ]),
                    'method' => 'post',
                    'id' => "{%id%}",
                    'course_id' => "{%course_id%}",
                ],
                'onclick' => 'deleteKnowledge($(this));'
            ]) ?>
            <a href="javascript:;" class="handle"><i class="fa fa-arrows"></i></a>
        </div>
    </div>
</li>

<script type="text/javascript">
    
    function showModals(_this){
        showModal(_this.attr("href"));
    }
    
</script>