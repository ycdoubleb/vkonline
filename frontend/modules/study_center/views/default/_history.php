<li class="{%className%}">
    <div class="pic keep-left">
        <a href="/course/default/view?id={%courseId%}" title="{%name%}">{%isExist%}</a>
    </div>
    <div class="text keep-right">
        <div class="tuip title single-clamp keep-left">{%name%}</div>
        <div class="tuip speaker">
            <a href="/teacher/default/view?id={%teacherId%}">
                <div class="avatars img-circle keep-left">
                    <img src="{%teacherAvatar%}" class="img-circle" width="25" height="25">
                </div>
                <span class="keep-left">{%teacherName%}</span>
            </a>
            <span class="font-success keep-right">{%number%} 人在学</span>
        </div>
        <div class="tuip single-clamp">
            <span>已完成 {%percent%}%</span>
            <div class="progress">
                <div class="progress-bar" style="width: {%percent%}%;"></div>
            </div>
            <span class="font-success">上次观看至&nbsp;
                {%nodeName%}-{%videoName%}&nbsp;{%lastTime%}
            </span>
        </div>
    </div>
    <a href="../default/view?id={%id%}" class="btn btn-success study keep-right">继续学习</a>
</li>