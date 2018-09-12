<li class="list-panel">
    <div class="list-header">
        <a class="icon" data-video_id="{%video_id%}" onclick="removeItem($(this))"><i class="fa fa-times"></i></a>
        <a href="../default/video-info?id={%video_id%}" title="{%name%}" target="_blank">
            <img src="{%img%}" width="100%" height="100%" />
        </a>
        <div class="hyalinize duration">{%duration%}</div>
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
        </div>
    </div>
</li>