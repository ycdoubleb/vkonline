<li class="{%className%}">
    <div class="pic">
        <a class="icon" data-id="{%id%}" onclick="removeItem($(this))"><i class="fa fa-times"></i></a>
        <a href="/course/default/view?id={%id%}" title="{%name%}" target="_blank">{%isExist%}</a>
    </div>
    <div class="text">
        <div class="tuip">
            <span class="title title-size single-clamp keep-left">{%name%}</span>
            <span class="keep-right">{%contentTime%}</span>
        </div>
        <div class="tuip single-clamp">{%tags%}</div>
        <div class="tuip">
            <span class="font-success keep-left">{%customerName%}</span>
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
        </div>
    </div>
</li>