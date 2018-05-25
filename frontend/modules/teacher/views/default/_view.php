<div class="item {%className%}">
    <a href="/course/default/view?id={%id%}">
        <div class="pic">
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
                <span class="tuip-green">{%customerName%}</span>
                <span class="tuip-right tuip-green">{%number%} 人在学</span>
            </div>
        </div>
    </a>
    <div class="speaker">
        <div class="tuip">
            <div class="avatar img-circle">
                <img src="{%teacherAvatar%}" class="img-circle" width="25" height="25">
            </div>
            <span class="tuip-left">{%teacherName%}</span>
            <span class="avg-star tuip-red tuip-right">{%avgStar%} 分</span>
        </div>
    </div>
</div>