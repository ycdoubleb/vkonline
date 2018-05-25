<li class="{%className%}">
    <div class="pic">
        {%isShow%}
        <a href="/course/default/view?id={%id%}" title="{%name%}" target="_blank">{%isExist%}</a>
    </div>
    <div class="text">
        <div class="tuip">
            <span class="title title-size single-clamp keep-left">{%name%}</span>
            <span class="keep-right">{%contentTime%}</span>
        </div>
        <div class="tuip single-clamp">{%tags%}</div>
        <div class="tuip">
            <span class="keep-left font-{%colorName%}">{%publishStatus%}</span>
            <span class="font-success keep-right">{%number%} 人在学</span>
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
            <span class="avg-star font-warning keep-right">{%avgStar%} 分</span>
            <a href="../default/view?id={%id%}" class="btn btn-info edit keep-right" style="display: none" target="_blank">编辑</a>
        </div>
    </div>
</li>