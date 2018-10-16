<!--视频list面板-->
<li class="list-panel">
    <div class="list-header">
        <button id="copy_{%id%}" class="btn btn-default btn-sm copy-video_id" data-clipboard-text="{%id%}" onclick="copyVideoId($(this))">复制ID</button>
        <a href="../video/view?id={%id%}" title="{%name%}" target="_blank">
            <img src="{%img%}" width="100%" height="100%" />
        </a>
        <div class="hyalinize status">{%status%}</div>
        <div class="hyalinize duration">{%duration%}</div>
    </div>
    <div class="list-body">
        <div class="tuip single-clamp">
            <input type="checkbox" class="hidden " name="Video[id]" value="{%id%}" />
            <span class="title">{%name%}</span>
        </div>        
    </div>
</li>