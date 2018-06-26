<li class="{%className%}">
    <div class="pic">
        <a href="/study_center/default/video-info?id={%id%}" title="{%name%}" target="_blank">{%isExist%}</a>
        <div class="duration">{%duration%}</div>
    </div>
    <div class="text">
        <div class="tuip title single-clamp">{%name%}</div>
        <div class="tuip single-clamp">{%tags%}</div>
        <div class="tuip">
            <span class="keep-left">{%createdAt%}</span>
            <span class="keep-right font-danger">{%levelName%}</span>
        </div>
        <div class="tuip des hidden">{%des%}</div>
    </div>
    <div class="teacher">
        <div class="tuip">
            <a href="/teacher/default/view?id={%teacherId%}" target="_blank">
                <div class="avatars img-circle keep-left">
                    <img src="{%teacherAvatar%}" class="img-circle" width="25" height="25" />
                </div>
                <span class="keep-left">{%teacherName%}</span>
            </a>
        </div>
    </div>
</li>