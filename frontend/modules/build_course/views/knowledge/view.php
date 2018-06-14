<li id="{%id%}">
    <div class="head">
        <a><span class="name">{%name%}</span></a>
        <div class="icongroup">
            <a href="/study_center/default/view?id={%id%}" target="_blank">
                <i class="fa fa-eye"></i>
            </a>
            <a href="../video/update?id={%id%}" onclick="showModal($(this));return false;">
                <i class="fa fa-pencil"></i>
            </a>
            <a href="../video/delete?id={%id%}" onclick="showModal($(this));return false;">
                <i class="fa fa-times"></i>
            </a>
            <a href="javascript:;" class="handle"><i class="fa fa-arrows"></i></a>
        </div>
    </div>
</li>