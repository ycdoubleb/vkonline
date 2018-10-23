<!--音频list面板-->
<li class="list-panel">
    <div class="list-header" style="text-align: center;">
        <button id="copy_{%id%}" class="btn btn-default btn-sm copy-video_id" data-clipboard-text="{%id%}" onclick="copyVideoId($(this))">复制ID</button>
        <a href="../document/view?id={%id%}" title="{%name%}" target="_blank">
            <img src="{%img%}" width="125" height="125" />
        </a>
    </div>
    <div class="list-body">
        <div class="tuip single-clamp">
            <input type="checkbox" class="hidden " name="Document[id]" value="{%id%}" />
            <span class="title">{%name%}</span>
        </div>        
    </div>
</li>