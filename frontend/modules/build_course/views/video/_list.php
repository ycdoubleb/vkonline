<li class="{%className%}">
    <div class="pic">
        <a href="{%url%}" title="{%name%}" target="_blank">{%isExist%}</a>
        <div class="duration">{%duration%}</div>
    </div>
    <div class="text">
        <div class="tuip title single-clamp">{%name%}</div>
        <div class="tuip single-clamp">{%tags%}</div>
        <div class="tuip">
            <span class="font-success keep-left">{%createdAt%}</span>
            <span class="btn-tuip keep-right bg-{%colorName%}">{%isRef%}</span>
        </div>
    </div>
    <div class="teacher">
        <div class="tuip">
            <a href="/teacher/default/view?id={%teacherId%}" target="_blank">
                <div class="avatars img-circle keep-left">
                    <img src="{%teacherAvatar%}" class="img-circle" width="25" height="25" />
                </div>
                <span class="keep-left">{%teacherName%}</span>
            </a>
            <span class="keep-right"><i class="fa fa-eye"></i>　{%playNum%}</span>
        </div>
    </div>
</li>