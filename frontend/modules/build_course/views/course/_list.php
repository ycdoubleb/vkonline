<div class="item {%className%}">
    <a href="../course/view?id={%id%}">
        <div class="pic">
            {%isShow%}
            {%isExist%}
        </div>
        <div class="cont">
            <div class="tuip">
                <span class="single-clamp tuip-name" title="{%name%}">{%name%}</span>
                <span class="tuip-right">{%contentTime%}</span>
            </div>
            <div class="single-clamp tuip">
                <span>{%tags%}</span>
            </div>
            <div class="tuip">
                <span class="tuip-{%colorName%}">{%publishStatus%}</span>
                <span class="tuip-right tuip-green">{%number%} 人在学</span>
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
            <span class="avg-star tuip-red tuip-right">{%avgStar%} 分</span>
            <a href="/course/default/view?id={%id%}" class="btn btn-info preview tuip-right" target="_blank">预览</a>
        </div>
    </div>
</div>