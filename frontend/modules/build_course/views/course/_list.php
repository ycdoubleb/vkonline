<li class="list-panel">
    <div class="list-header">
        <div class="icon text-danger {%is_hidden%}"><i class="fa fa-lock"></i></div>
        <a href="/course/default/view?id={%id%}" title="{%name%}" target="_blank">
            <img src="{%cover_img%}" width="100%" height="100%" />
        </a>
    </div>
    <div class="list-body">
        <div class="tuip">
            <span class="title single-clamp">{%name%}</span>
        </div>
        <div class="tuip single-clamp">{%tags%}</div>
        <div class="tuip">
            <span class="text-{%color_name%} pull-left">{%is_publish%}</span>
            <span class="text-success pull-right">{%learning_count%} 人在学</span>
        </div>
    </div>
    <div class="list-footer">
        <div class="tuip">
            <a href="/teacher/default/view?id={%teacher_id%}" target="_blank">
                <img src="{%teacher_avatar%}" class="avatars img-circle pull-left" />
                <span class="pull-left">{%teacher_name%}</span>
            </a>
            <span class="avg-star text-danger pull-right">{%avg_star%} 分</span>
            <a href="../course/view?id={%id%}" class="btn btn-info btn-flat btn-edit pull-right" target="_blank">
                <?= Yii::t('app', 'Edit') ?>
            </a>
        </div>
    </div>
</li>