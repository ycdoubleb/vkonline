<li class="list-panel">
    <div class="list-header">
        <a class="icon" data-videoid="{%course_id%}" onclick="removeItem($(this))"><i class="fa fa-times"></i></a>
        <a href="/course/default/view?id={%course_id%}" title="{%name%}" target="_blank">
            <img src="{%cover_img%}" width="100%" height="100%" />
        </a>
    </div>
    <div class="list-body">
        <div class="tuip">
            <span class="title single-clamp">{%name%}</span>
        </div>
        <div class="tuip single-clamp">{%tags%}</div>
        <div class="tuip">
            <span class="text-success">{%customer_name%}</span>
        </div>
    </div>
    <div class="list-footer">
        <div class="tuip">
            <a href="/teacher/default/view?id={%teacher_id%}" target="_blank">
                <img src="{%teacher_avatar%}" class="avatars img-circle pull-left" />
                <span class="pull-left">{%teacher_name%}</span>
            </a>
            <span class="avg-star text-danger pull-right">{%avg_star%} 分</span>
        </div>
    </div>
</li>