<!--视频list面板-->
<li class="list-panel">
    <div class="list-header">
        <a href="/study_center/default/video-info?id={%id%}" title="{%name%}" target="_blank">
            <img src="{%img%}" width="100%" height="100%" />
        </a>
        <div class="hyalinize duration">{%duration%}</div>
    </div>
    <div class="list-body">
        <div class="tuip">
            <span class="title single-clamp" style="width: 80%">{%name%}</span>
            <span class="text-success pull-right">{%status%}</span>
        </div>
        <div class="tuip single-clamp">{%tags%}</div>
        <div class="tuip">
            <span class="text-success pull-left">{%created_at%}</span>
            <span class="text-danger pull-right">{%level_name%}</span>
        </div>
        <div class="tuip des hidden">{%des%}</div>
    </div>
    <div class="list-footer">
        <div class="tuip">
            <a href="/teacher/default/view?id={%teacher_id%}" target="_blank">
                <img src="{%teacher_avatar%}" class="avatars img-circle pull-left" />
                <span class="pull-left">{%teacher_name%}</span>
            </a>
            <a href="../video/view?id={%id%}" class="btn btn-info btn-flat btn-edit pull-right" target="_blank">
                <?= Yii::t('app', 'Edit') ?>
            </a>
        </div>
    </div>
</li>