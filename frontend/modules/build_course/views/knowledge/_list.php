<!--引用视频list面板-->
<li class="list-panel">
    <div class="list-header">
        <a href="/study_center/default/video-info?id={%id%}" title="{%name%}" target="_blank">
            <img src="{%img%}" width="100%" height="100%" />
        </a>
        <div class="hyalinize">{%duration%}</div>
    </div>
    <div class="list-body">
        <span class="title single-clamp pull-left">{%name%}</span>
        <a class="btn btn-primary btn-sm choice pull-right" href="../knowledge/choice?video_id={%id%}" onclick="clickChoiceEvent($(this)); return false;">选择</a>
    </div>
</li>