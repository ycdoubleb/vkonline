<div class="item {%className%}">
    <a href="../video/view?id={%id%}">
        <div class="pic">
            {%isExist%}
            <div class="duration">{%duration%}</div>
        </div>
        <div class="cont">
            <div class="tuip">
                <span class="tuip-name">{%courseName%}&nbsp;&nbsp;{%name%}</span>
            </div>
            <div class="tuip">
                <span>{%tags%}</span>
            </div>
            <div class="tuip">
                <span>{%createdAt%}</span>
                <span class="tuip-right tuip-bg-{%colorName%}">{%isRef%}</span>
            </div>
        </div>
    </a>
    <div class="speaker">
        <div class="tuip">
            <div class="avatar img-circle">
                <img src="{%teacherAvatar%}" class="img-circle" width="25" height="25">
            </div>
            <span class="tuip-left">{%teacherName%}</span>
            <span class="tuip-right"><i class="fa fa-eye"></i>　{%playNum%}</span>
        </div>
    </div>
</div>