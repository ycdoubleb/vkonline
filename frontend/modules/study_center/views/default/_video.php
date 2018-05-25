<li class="{%className%}">
    <div class="pic">
        <a class="icon" data-courseid="{%courseId%}" data-videoid="{%id%}" onclick="removeItem($(this))"><i class="fa fa-times"></i></a>
        <a href="/study_center/default/view?id={%id%}" title="{%name%}">{%isExist%}</a>
        <div class="duration">{%duration%}</div>
    </div>
    <div class="text">
        <div class="tuip title single-clamp">{%name%}</div>
        <div class="tuip single-clamp">{%tags%}</div>
        <div class="tuip">{%customerName%}</div>
    </div>
    <div class="teacher">
        <div class="tuip">
            <a href="/teacher/default/view?id={%teacherId%}">
                <div class="avatars img-circle keep-left">
                    <img src="{%teacherAvatar%}" class="img-circle" width="25" height="25" />
                </div>
                <span class="keep-left">{%teacherName%}</span>
            </a>
            <span class="keep-right"><i class="fa fa-eye"></i>　{%playNum%}</span>
        </div>
    </div>
</li>