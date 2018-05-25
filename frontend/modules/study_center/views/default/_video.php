<div class="item {%className%}">
    <a href="../video/view?id={%id%}">
        <div class="pic">
            {%isExist%}
            <div class="duration">{%duration%}</div>
        </div>
        <div class="cont">
            <div class="tuip">
                <span class="single-clamp tuip-name" title="{%courseName%}&nbsp;&nbsp;{%name%}">{%courseName%}&nbsp;&nbsp;{%name%}</span>
            </div>
            <div class="single-clamp tuip">
                <span>{%tags%}</span>
            </div>
            <div class="tuip">
                <span class="tuip-green">{%customerName%}</span>
            </div>
        </div>
    </a>
    <div class="speaker">
        <div class="tuip">
            <a href="/teacher/default/view?id={%teacherId%}">
                <div class="avatar img-circle">
                    <img src="{%teacherAvatar%}" class="img-circle" width="25" height="25">
                </div>
                <span class="tuip-left">{%teacherName%}</span>
            </a>
            <span class="tuip-right"><i class="fa fa-eye"></i>　{%playNum%}</span>
        </div>
    </div>
</div>