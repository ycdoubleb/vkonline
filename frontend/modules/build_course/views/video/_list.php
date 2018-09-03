<!--视频list面板-->
<li class="list-panel">
    <div class="list-header">
        <a href="../video/view?id={%id%}" title="{%name%}" target="_blank">
            <img src="{%img%}" width="100%" height="100%" />
        </a>
        <div class="hyalinize level">{%level_name%}</div>
        <div class="hyalinize status">{%status%}</div>
        <div class="hyalinize duration">{%duration%}</div>
    </div>
    <div class="list-body">
        <div class="tuip">
            <input type="checkbox" class="hidden pull-left" name="Video[id]" value="{%id%}" />
            <span class="title single-clamp pull-right">{%name%}</span>
        </div>        
    </div>
</li>