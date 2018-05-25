<div class="item {%className%}">
    <div class="pic">
        {%isExist%}
    </div>
    <div class="cont">
        <div class="tuip">
            <span class="single-clamp tuip-name" title="{%name%}">{%name%}</span>
        </div>
        <div class="speaker">
            <div class="tuip">
                <a href="/teacher/default/view?id={%teacherId%}">
                    <div class="avatar img-circle">
                        <img src="{%teacherAvatar%}" class="img-circle" width="25" height="25">
                    </div>
                    <span class="tuip-left">{%teacherName%}</span>
                </a>
                <span class="tuip-green tuip-right">{%number%} 人在学</span>
            </div>
        </div>
        <div class="tuip single-clamp">
            <span>已完成&nbsp;{%percent%}%</span>
            <div class="progress">
                <div class="progress-bar" style="width: {%percent%}%;">
                </div>
            </div>
            <span class="single-clamp tuip-green">上次观看至&nbsp;
                {%nodeName%}-{%videoName%}影调&nbsp;{%lastTime%}</span>
        </div>
    </div>
    <a href="../default/view?id={%id%}" class="btn btn-success tuip-right study">继续学习</a>
</div>
        
        