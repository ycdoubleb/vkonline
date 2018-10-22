<!--引用视频list面板-->
<li class="list-panel">
    <div class="list-header">
        <a href="../knowledge/choice?video_id={%id%}" title="{%name%}" onclick="clickChoiceEvent($(this)); return false;">
            <img src="{%img%}" width="100%" height="100%" />
        </a>
        <div class="hyalinize level">{%level%}</div>
        <div class="hyalinize status">{%status%}</div>
        <div class="hyalinize duration">{%duration%}</div>
    </div>
    <div class="list-body">
        <div class="tuip">
            <span class="title single-clamp pull-right">{%name%}</span>
        </div>        
    </div>
</li>