<li class="list-panel">
    <div class="list-header pull-left">
        <a href="/course/default/view?id={%course_id%}" title="{%name%}" target="_blank">
            <img src="{%cover_img%}" width="280" height="210" />
        </a>
    </div>
    <div class="list-body pull-ringt">
        <div class="tuip">
            <span class="title single-clamp">{%name%}</span>
        </div>
        <div class="tuip" style="line-height: 25px;">
            <a href="/teacher/default/view?id={%teacher_id%}" target="_blank">
                <img src="{%teacher_avatar%}" class="avatars img-circle pull-left"/>
                <span class="pull-left">{%teacher_name%}</span>
            </a>
            <span class="text-success pull-right">{%learning_count%} 人在学</span>
        </div>
        <div class="tuip single-clamp">
            <span>已完成 {%percent%}%</span>
            <div class="progress">
                <div class="progress-bar" style="{%progress_width%}"></div>
            </div>
            <span class="text-success">上次观看至&nbsp;
                {%node_name%}-{%knowledge_name%}&nbsp;{%data%}
            </span>
        </div>
    </div>
    <div class="list-footer pull-right">
        <a href="/study_center/default/view?id={%last_knowledge%}" class="btn btn-success study pull-right" target="_blank">继续学习</a>
    </div>
</li>