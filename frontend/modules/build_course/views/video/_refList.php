<div class="item reference {%className%}">
    <a href="{%url%}">
        <div class="pic">
            {%isExist%}
            <div class="duration">{%duration%}</div>
        </div>
    </a>
    <div class="cont">
        <span class="single-clamp tuip-name" title="{%name%}">{%name%}</span>
        <a class="btn btn-primary btn-sm choice tuip-right" href="{%url%}" onclick="clickChoiceEvent($(this)); return false;">选择</a>
    </div>
</div>